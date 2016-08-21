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
use AppBundle\Entity\Report;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
     * @Route("/seguimiento/informe/{id}", name="admin_group_agreement_report_form", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_REPORT', agreement)")
     */
    public function studentReportAction(Agreement $agreement, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (null === $agreement->getReport()) {
            $report = new Report();
            $report->setSignDate(new \DateTime());
            $report->setAgreement($agreement);
            $em->persist($report);
        } else {
            $report = $agreement->getReport();
        }

        $form = $this->createForm('AppBundle\Form\Type\ReportType', $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            if ($request->request->has('submit')) {
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'student'));
            }
            return $this->redirectToRoute(
                $request->request->has('submit') ? 'admin_group_student_calendar' : 'admin_group_agreement_report_download',
                ['id' => $agreement->getId()]);
        }

        return $this->render('group/report_form.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
            'breadcrumb' => [
                ['fixed' => $agreement->getStudent()->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $agreement->getStudent()->getStudentGroup()->getId()]],
                ['fixed' => (string) $agreement->getStudent(), 'path' => 'admin_group_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'admin_group_student_calendar', 'options' => ['id' => $agreement->getId()]],
                ['fixed' => $this->get('translator')->trans('form.report', [], 'student')]
            ],
            'form' => $form->createView(),
            'agreement' => $agreement
        ]);
    }
}
