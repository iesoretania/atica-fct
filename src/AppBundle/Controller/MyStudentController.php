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
use AppBundle\Entity\Report;
use AppBundle\Entity\User;
use AppBundle\Entity\Workday;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MyStudentController extends BaseController
{
    /**
     * @Route("/estudiantes", name="my_student_index", methods={"GET"})
     */
    public function myStudentIndexAction()
    {
        $users = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getAgreementRelatedStudents($this->getUser());

        if (count($users) === 1) {
            return $this->myStudentAgreementIndexAction($users[0][0]);
        }
        return $this->render('student/index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
            'title' => null,
            'elements' => $users
        ]);
    }

    /**
     * @Route("/estudiantes/{id}", name="my_student_agreements", methods={"GET"})
     * @Security("is_granted('USER_TRACK', student)")
     */
    public function myStudentAgreementIndexAction(User $student)
    {
        $users = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getAgreementRelatedStudents($this->getUser());
        $agreements = $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getTutorizedAgreements($student, $this->getUser());

        if (count($agreements) == 1) {
            return $this->myStudentAgreementCalendarAction($agreements[0]);
        }

        $title = $student->getFullDisplayName();
        return $this->render('student/calendar_agreement_select.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
                'breadcrumb' => [
                    ['fixed' => $title]
                ],
                'student' => $student,
                'title' => $title,
                'elements' => $agreements,
                'route_name' => 'my_student_agreement_calendar',
                'back_route_name' => count($users) === 1 ? 'frontpage' : 'my_student_index',
                'back_route_params' => [],
            ]);
    }

    /**
     * @Route("/estudiantes/seguimiento/{id}", name="my_student_agreement_calendar", methods={"GET"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function myStudentAgreementCalendarAction(Agreement $agreement)
    {
        $users = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getAgreementRelatedStudents($this->getUser());
        $calendar = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workday')->getArrayCalendar($agreement->getWorkdays());
        $agreements = $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getTutorizedAgreements($agreement->getStudent(), $this->getUser());
        $title = (string) $agreement->getWorkcenter();

        $parent = (count($users) === 1 ? 'frontpage' : 'my_student_index');

        return $this->render('student/tutor_calendar_agreement.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
                'breadcrumb' => [
                    ['fixed' => $agreement->getStudent()->getFullDisplayName(), 'path' => 'my_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                    ['fixed' => $title],
                ],
                'title' => $title,
                'user' => $this->getUser(),
                'calendar' => $calendar,
                'agreement' => $agreement,
                'route_name' => 'my_student_agreement_tracking',
                'back_route_name' => count($agreements) === 1 ? $parent : 'my_student_agreements',
                'back_route_params' => count($agreements) === 1 ? [] : ['id' => $agreement->getStudent()->getId()],
                'activities_stats' => $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->getActivitiesStats($agreement)
            ]);
    }

    /**
     * @Route("/estudiantes/seguimiento/{id}/operacion", name="my_student_agreement_calendar_operation", methods={"POST"})
     * @Security("is_granted('AGREEMENT_LOCK', agreement) or is_granted('AGREEMENT_UNLOCK', agreement)")
     */
    public function operationWorkdayAction(Agreement $agreement, Request $request)
    {
        if ($request->request->has('week_lock') || ($request->request->has('week_unlock'))) {
            return $this->lockWeekAction($agreement, $request, $request->request->has('week_lock'), 'my_student_agreement_calendar');
        } else {
            return $this->lockWorkdayAction($agreement, $request, $request->request->has('lock'), 'my_student_agreement_calendar');
        }
    }
    /**
     * @Route("/estudiantes/seguimiento/jornada/{id}", name="my_student_agreement_tracking", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_ACCESS', workday.getAgreement())")
     */
    public function studentWorkdayAction(Workday $workday, Request $request)
    {
        $agreement = $workday->getAgreement();
        return $this->baseWorkdayAction($workday, $request, [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
            'breadcrumb' => [
                ['fixed' => $agreement->getStudent()->getFullDisplayName(), 'path' => 'my_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'my_student_agreement_calendar', 'options' => ['id' => $workday->getAgreement()->getId()]],
                ['fixed' => $workday->getDate()->format('d/m/Y')]
            ],
            'back_route_name' => 'my_student_agreement_calendar'
        ]);
    }
    
    /**
     * @Route("/estudiantes/informe/{id}", name="my_student_agreement_report_form", methods={"GET", "POST"})
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
                $request->request->has('submit') ? 'my_student_agreement_calendar' : 'my_student_agreement_report_download',
                ['id' => $agreement->getId()]);
        }

        return $this->render('student/report_form.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
            'breadcrumb' => [
                ['fixed' => $agreement->getStudent()->getFullDisplayName(), 'path' => 'my_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'my_student_agreement_calendar', 'options' => ['id' => $agreement->getId()]],
                ['fixed' => $this->get('translator')->trans('form.report', [], 'student')]
            ],
            'form' => $form->createView(),
            'agreement' => $agreement
        ]);
    }

    /**
     * @Route("/estudiantes/informe/descargar/{id}", name="my_student_agreement_report_download", methods={"GET"})
     * @Security("is_granted('AGREEMENT_REPORT', agreement)")
     */
    public function downloadStudentReportAction(Agreement $agreement)
    {
        $translator = $this->get('translator');

        $title = $translator->trans('form.report', [], 'student') . ' ' . $agreement->getStudent();

        $mpdf = $this->get('sasedev_mpdf');
        $mpdf->init();
        $this->fillWorkingTutorReport($mpdf, $translator, $agreement, $title);

        $title = str_replace(' ', '_', $title);
        return $mpdf->generateInlineFileResponse($title . '.pdf');
    }
}
