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
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StudentController extends Controller
{
    /**
     * @Route("/estudiante/{id}", name="student_detail", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_GROUP_ADMIN', student.getStudentGroup())")
     */
    public function studentIndexAction(User $student, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\StudentUserType', $student, [
            'admin' => !$this->isGranted('ROLE_ADMIN')
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
     * @Route("/calendario", name="student_calendar", methods={"GET"})
     */
    public function studentCalendarIndexAction()
    {
        $agreements = $this->getUser()->getStudentAgreements();

        if (count($agreements) == 1) {
            return $this->studentCalendarAgreementIndexAction($agreements[0]);
        }

        return $this->render('student/calendar_agreement_select.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('student_calendar'),
                'user' => $this->getUser(),
                'elements' => $agreements
            ]);
    }

    /**
     * @Route("/calendario/{id}", name="student_calendar_agreement", methods={"GET"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function studentCalendarAgreementIndexAction(Agreement $agreement)
    {
        $calendar = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workday')->getArrayCalendar($agreement->getWorkdays());
        $title = (string) $agreement->getWorkcenter();

        return $this->render('student/calendar_agreement.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('student_calendar'),
                'breadcrumb' => [
                    ['fixed' => $title],
                ],
                'title' => $title,
                'user' => $this->getUser(),
                'calendar' => $calendar,
                'agreement' => $agreement
            ]);
    }
}
