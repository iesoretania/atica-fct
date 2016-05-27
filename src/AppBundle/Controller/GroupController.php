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
use AppBundle\Entity\Tracking;
use AppBundle\Entity\User;
use AppBundle\Entity\Workday;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/grupos")
 * @Security("is_granted('ROLE_GROUP_TUTOR')")
 */
class GroupController extends Controller
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
     * @Route("/{id}", name="admin_group_students", methods={"GET"})
     * @Security("is_granted('ROLE_GROUP_ADMIN', group)")
     */
    public function groupDetailIndexAction(Group $group, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $usersQuery = $em->createQuery('SELECT u FROM AppBundle:User u WHERE u.studentGroup = :group')
            ->setParameter('group', $group);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'u.lastName',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('group/manage_students.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'breadcrumb' => [
                    ['fixed' => $group->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $group->getId()]],
                ],
                'title' => $group->getName(),
                'pagination' => $pagination,
                'user' => $this->getUser()
            ]);
    }

    /**
     * @Route("/{id}/seguimiento", name="admin_group_student_agreements", methods={"GET"})
     * @Security("is_granted('ROLE_GROUP_ADMIN', student.getStudentGroup())")
     */
    public function studentAgreementIndexAction(User $student)
    {
        $agreements = $student->getStudentAgreements();

        /*if (count($agreements) == 1) {
            return $this->studentAgreementCalendarIndexAction($agreements[0]);
        }*/

        return $this->render('student/calendar_agreement_select.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'breadcrumb' => [
                    ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                    ['fixed' => (string) $student],
                ],
                'user' => $this->getUser(),
                'elements' => $agreements,
                'route_name' => 'admin_group_student_calendar'
            ]);
    }

    /**
     * @Route("/seguimiento/{id}", name="admin_group_student_calendar", methods={"GET"})
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

    public function lockWorkdayAction(Agreement $agreement, Request $request, $status)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->request->has('ids')) {
            try {
                $ids = $request->request->get('ids');

                $em->createQuery('UPDATE AppBundle:Workday w SET w.locked = :locked WHERE w.id IN (:ids) AND w.agreement = :agreement')
                    ->setParameter('locked', $status)
                    ->setParameter('ids', $ids)
                    ->setParameter('agreement', $agreement)
                    ->execute();

                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.locked', [], 'calendar'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.locked_error', [], 'calendar'));
            }
        }
        return $this->redirectToRoute('admin_group_student_calendar', ['id' => $agreement->getId()]);
    }

    /**
     * @Route("/seguimiento/{id}/operacion", name="admin_group_student_calendar_operation", methods={"POST"})
     * @Security("is_granted('AGREEMENT_MANAGE', agreement)")
     */
    public function deleteAgreementCalendarAction(Agreement $agreement, Request $request)
    {
        return $this->lockWorkdayAction($agreement, $request, $request->request->has('lock'));
    }

    /**
     * @Route("/seguimiento/jornada/{id}", name="admin_group_student_tracking", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_ACCESS', workday.getAgreement())")
     */
    public function studentWorkdayAction(Workday $workday, Request $request)
    {
        $student = $workday->getAgreement()->getStudent();

        $agreementHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->countHours($workday->getAgreement());

        $dow = ((6 + (int) $workday->getDate()->format('w')) % 7);

        $title = $this->get('translator')->trans('dow' . $dow, [], 'calendar') . ', ' . $workday->getDate()->format('d/m/Y');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AppBundle:Tracking')->updateTrackingByWorkday($workday);

        $form = $this->createForm('AppBundle\Form\Type\WorkdayTrackingType', $workday);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Tracking $tracking */
            foreach ($workday->getTrackingActivities() as $tracking) {
                if ($tracking->getHours() == 0) {
                    $workday->removeTrackingActivity($tracking);
                    $em->remove($tracking);
                } else {
                    $em->persist($tracking);
                }
            }
            $em->flush();
        }

        $activitiesStats = $em->getRepository('AppBundle:Agreement')->getActivitiesStats($workday->getAgreement());
        $next = $em->getRepository('AppBundle:Workday')->getNext($workday);
        $previous = $em->getRepository('AppBundle:Workday')->getPrevious($workday);

        return $this->render('student/tracking_form.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'breadcrumb' => [
                    ['fixed' => $student->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $student->getStudentGroup()->getId()]],
                    ['fixed' => (string) $student, 'path' => 'admin_group_student_agreements', 'options' => ['id' => $student->getId()]],
                    ['fixed' => (string) $workday->getAgreement()->getWorkcenter(), 'path' => 'admin_group_student_calendar', 'options' => ['id' => $workday->getAgreement()->getId()]],
                    ['fixed' => $title],
                ],
                'title' => $title,
                'user' => $this->getUser(),
                'workday' => $workday,
                'form' => $form->createView(),
                'agreement_hours' => $agreementHours,
                'activities_stats' => $activitiesStats,
                'next' => $next,
                'previous' => $previous,
                'back_route_name' => 'admin_group_student_calendar'
            ]);
    }
}
