<?php
/*
  ÁTICA - Aplicación web para la gestión documental de centros educativos

  Copyright (C) 2015-2016: Luis Ramón López López

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].
*/

namespace AppBundle\Controller;

use AppBundle\Entity\Agreement;
use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use AppBundle\Entity\Workday;
use AppBundle\Form\Model\Calendar;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/alumnado")
 * @Security("is_granted('ROLE_GROUP_TUTOR')")
 */
class GroupController extends BaseController
{
    /**
     * @Route("", name="admin_tutor_group", methods={"GET"})
     */
    public function groupIndexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $qb = $em->getRepository('AppBundle:Group')
            ->createQueryBuilder('g')
            ->innerJoin('g.training', 't')
            ->innerJoin('t.department', 'd')
            ->orderBy('d.name')
            ->addOrderBy('t.name')
            ->addOrderBy('g.name');

        if (!$user->isGlobalAdministrator()) {
            $qb = $qb
                ->where('g.id IN (:groups)')
                ->orWhere('d.head = :user')
                ->setParameter('groups', $user->getTutorizedGroups()->toArray())
                ->setParameter('user', $user);
        }

        $items = $qb->getQuery()->getResult();

        if (count($items) == 1) {
            return $this->groupDetailIndexAction($items[0], $request);
        }

        return $this->render('group/group_index.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'title' => null,
                'elements' => $items
            ]);
    }

    /**
     * @Route("/grupo/{id}", name="admin_group_students", methods={"GET"})
     * @Security("is_granted('GROUP_MANAGE', group)")
     */
    public function groupDetailIndexAction(Group $group, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $usersQuery = $em->createQuery('SELECT u FROM AppBundle:User u WHERE u.studentGroup = :group')
            ->setParameter('group', $group);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'u.lastName',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('group/manage_students.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
            'breadcrumb' => [
                ['fixed' => (string) $group],
            ],
            'title' => $group->getName(),
            'pagination' => $pagination
        ]);

    }

    /**
     * @Route("/alumnado/estudiante/{id}", name="student_detail", methods={"GET", "POST"})
     * @Security("is_granted('GROUP_MANAGE', student.getStudentGroup())")
     */
    public function studentIndexAction(User $student, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\StudentUserType', $student, [
            'admin' => $this->isGranted('ROLE_ADMIN')
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Guardar el usuario en la base de datos

            // Probar a guardar los cambios
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'student'));
                return $this->redirectToRoute('admin_group_students', ['id' => $student->getStudentGroup()->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'student'));
            }
        }
        return $this->render('group/form_student.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'breadcrumb' => [
                    ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                    ['fixed' => (string) $student],
                ],
                'title' => (string) $student,
                'user' => $student,
                'form' => $form->createView()
            ]);
    }

    /**
     * @Route("/seguimiento/seleccion/{id}", name="admin_group_student_agreements", methods={"GET"})
     * @Security("is_granted('GROUP_MANAGE', student.getStudentGroup())")
     */
    public function studentAgreementIndexAction(User $student)
    {
        $agreements = $student->getStudentAgreements();

        return $this->render('group/calendar_agreement_select.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'breadcrumb' => [
                    ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                    ['fixed' => (string) $student],
                ],
                'student' => $student,
                'elements' => $agreements,
                'route_name' => 'admin_group_student_calendar'
            ]);
    }

    /**
     * @Route("/seguimiento/acuerdo/{id}", name="admin_group_student_calendar", methods={"GET"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function studentCalendarAgreementIndexAction(Agreement $agreement)
    {
        $student = $agreement->getStudent();

        $calendar = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workday')->getArrayCalendar($agreement->getWorkdays());
        $title = (string) $agreement->getWorkcenter();

        return $this->render('group/calendar_agreement.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'breadcrumb' => [
                    ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                    ['fixed' => (string) $student, 'path' => 'admin_group_student_agreements', 'options' => ['id' => $student->getId()]],
                    ['fixed' => $title],
                ],
                'title' => $title,
                'user' => $this->getUser(),
                'calendar' => $calendar,
                'agreement' => $agreement,
                'route_name' => 'admin_group_student_tracking'
            ]);
    }

    public function deleteWorkdayAction(Agreement $agreement, Request $request)
    {
        $this->denyAccessUnlessGranted('AGREEMENT_MANAGE', $agreement);

        $em = $this->getDoctrine()->getManager();

        if ($request->request->has('ids')) {
            try {
                $ids = $request->request->get('ids');

                $dates = $em->getRepository('AppBundle:Workday')->createQueryBuilder('w')
                    ->where('w.id IN (:ids)')
                    ->andWhere('w.agreement = :agreement')
                    ->setParameter('ids', $ids)
                    ->setParameter('agreement', $agreement)
                    ->getQuery()
                    ->getResult();

                /** @var Workday $date */
                foreach ($dates as $date) {
                    if ($date->getTrackedHours() === 0) {
                        $em->remove($date);
                    }
                }
                $em->flush();

                $agreement->setFromDate($this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getRealFromDate($agreement));
                $agreement->setToDate($this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getRealToDate($agreement));

                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.deleted', [], 'calendar'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], 'calendar'));
            }
        }
        return $this->redirectToRoute('admin_group_student_calendar', ['id' => $agreement->getId()]);
    }

    /**
     * @Route("/seguimiento/acuerdo/{id}/operacion", name="admin_group_student_workday_operation", methods={"POST"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function operationWorkdayAction(Agreement $agreement, Request $request)
    {
        if ($request->request->has('delete')) {
            return $this->deleteWorkdayAction($agreement, $request);
        } elseif ($request->request->has('week_lock') || ($request->request->has('week_unlock'))) {
            return $this->lockWeekAction($agreement, $request, $request->request->has('week_lock'), 'admin_group_student_calendar');
        } else {
            return $this->lockWorkdayAction($agreement, $request, $request->request->has('lock'), 'admin_group_student_calendar');
        }
    }

    /**
     * @Route("/seguimiento/acuerdo/{id}/incorporar", name="admin_group_student_workday_add", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_MANAGE', agreement)")
     */
    public function addAgreementCalendarAction(Agreement $agreement, Request $request)
    {
        $totalHours = $agreement->getStudent()->getStudentGroup()->getTraining()->getProgramHours();
        $agreementHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->countHours($agreement);
        $studentHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->countAgreementHours($agreement->getStudent());

        $calendar = new Calendar(max(0, $totalHours - $studentHours));

        $form = $this->createForm('AppBundle\Form\Type\CalendarType', $calendar, [
            'program_hours' => $totalHours
        ]);

        $form->handleRequest($request);

        $workdays = new ArrayCollection();

        if ($form->isValid() && $form->isSubmitted()) {
            $workdays = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workday')->createCalendar($calendar, $agreement);

            if ($request->request->has('submit')) {
                $this->getDoctrine()->getManager()->flush();
                $agreement->setFromDate($this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getRealFromDate($agreement));
                $agreement->setToDate($this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getRealToDate($agreement));
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'calendar'));
                return $this->redirectToRoute('admin_group_student_calendar', ['id' => $agreement->getId()]);
            }
        }

        $student = $agreement->getStudent();

        $calendar = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workday')->getArrayCalendar($workdays);

        return $this->render('group/calendar_agreement_workday_add.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
            'breadcrumb' => [
                ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                ['fixed' => (string) $student, 'path' => 'admin_group_student_agreements', 'options' => ['id' => $student->getId()]],
                ['fixed' => (string) $agreement->getWorkcenter()],
                ['fixed' => $this->get('translator')->trans('form.add', [], 'calendar')]
            ],
            'agreement' => $agreement,
            'total_hours' => $totalHours,
            'agreement_hours' => $agreementHours,
            'student_hours' => $studentHours,
            'form' => $form->createView(),
            'calendar' => $calendar
        ]);
    }

    /**
     * @Route("/seguimiento/acuerdo/jornada/{id}", name="admin_group_student_tracking", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_ACCESS', workday.getAgreement())")
     */
    public function studentWorkdayAction(Workday $workday, Request $request)
    {
        $student = $workday->getAgreement()->getStudent();

        return $this->baseWorkdayAction($workday, $request, [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
            'breadcrumb' => [
                ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                ['fixed' => (string) $student, 'path' => 'admin_group_student_agreements', 'options' => ['id' => $student->getId()]],
                ['fixed' => (string) $workday->getAgreement()->getWorkcenter(), 'path' => 'admin_group_student_calendar', 'options' => ['id' => $workday->getAgreement()->getId()]],
                ['fixed' => $workday->getDate()->format('d/m/Y')]
            ],
            'back_route_name' => 'admin_group_student_calendar'
        ]);
    }

    /**
     * @Route("/seguimiento/acuerdo/jornada/modificar/{id}", name="admin_group_student_workday_form", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_MANAGE', workday.getAgreement())")
     */
    public function agreementCalendarFormAction(Workday $workday, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\WorkdayType', $workday);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'calendar'));
            return $this->redirectToRoute('admin_group_student_calendar', ['id' => $workday->getAgreement()->getId()]);
        }
        $student = $workday->getAgreement()->getStudent();

        $dow = ((6 + (int) $workday->getDate()->format('w')) % 7);
        $title = $this->get('translator')->trans('dow' . $dow, [], 'calendar') . ', ' . $workday->getDate()->format('d/m/Y');

        return $this->render('group/calendar_agreement_workday_form.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
            'breadcrumb' => [
                ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                ['fixed' => (string) $student, 'path' => 'admin_group_student_agreements', 'options' => ['id' => $student->getId()]],
                ['fixed' => (string) $workday->getAgreement()->getWorkcenter(), 'path' => 'admin_group_student_calendar', 'options' => ['id' => $workday->getAgreement()->getId()]],
                ['fixed' => $title],
            ],
            'form' => $form->createView(),
            'workday' => $workday
        ]);
    }

    /**
     * @Route("/seguimiento/acuerdo/nuevo/{id}", name="admin_group_student_agreement_new", methods={"GET", "POST"})
     * @Security("is_granted('GROUP_CREATE_AGREEMENT', user.getStudentGroup())")
     */
    public function agreementNewFormAction(User $user, Request $request)
    {
        $agreement = new Agreement();
        $agreement->setStudent($user);
        $this->getDoctrine()->getManager()->persist($agreement);

        return $this->agreementFormAction($agreement, $request);
    }

    /**
     * @Route("/seguimiento/acuerdo/modificar/{id}", name="admin_group_student_agreement_form", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_MANAGE', agreement)")
     */
    public function agreementFormAction(Agreement $agreement, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\AgreementType', $agreement);

        $form->handleRequest($request);

        $student = $agreement->getStudent();

        if ($form->isValid() && $form->isSubmitted()) {
            // Probar a guardar los cambios
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'group'));
                return $this->redirectToRoute('admin_group_student_agreements', ['id' => $student->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'group'));
            }
        }

        $title = (null === $agreement->getId())
            ? $this->get('translator')->trans('form.new_agreement', [], 'group')
            : (string) $agreement->getWorkcenter();

        return $this->render('group/form_agreement.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'breadcrumb' => [
                    ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                    ['fixed' => (string) $student, 'path' => 'admin_group_student_agreements', 'options' => ['id' => $student->getId()]],
                    ['fixed' => $title]
                ],
                'form' => $form->createView(),
                'agreement' => $agreement
            ]);
    }

    /**
     * @Route("/seguimiento/acuerdo/eliminar/{id}", name="admin_group_student_agreement_delete", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_MANAGE', agreement)")
     */
    public function agreementDeleteAction(Agreement $agreement, Request $request)
    {
        $student = $agreement->getStudent();

        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            // Eliminar el acuerdo de la base de datos
            $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->delete($agreement);
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.agreement_deleted', [], 'group'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.agreement_not_deleted', [], 'group'));
            }
            return $this->redirectToRoute('admin_group_student_agreements', ['id' => $student->getId()]);
        }

        $title = (string) $agreement->getWorkcenter();

        return $this->render('group/delete_agreement.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
            'breadcrumb' => [
                ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                ['fixed' => (string) $student, 'path' => 'admin_group_student_agreements', 'options' => ['id' => $student->getId()]],
                ['fixed' => $title]
            ],
            'title' => $title,
            'agreement' => $agreement
        ]);
    }

    /**
     * @Route("/api/usuario/crear", name="api_user_new", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_DEPARTMENT_HEAD')")
     */
    public function apiNewUserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $newUser = new User();
        
        $form = $this->createForm('AppBundle\Form\Type\NewUserType', $newUser, [
            'action' => $this->generateUrl('api_user_new'),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $newUser
                ->setEnabled(true)
                ->setGlobalAdministrator(false);
            
            $em->persist($newUser);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'id' => $newUser->getId(),
                'name' => $newUser->getFullPersonDisplayName()
            ]);
        }

        return $this->render('group/new_user_partial.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
