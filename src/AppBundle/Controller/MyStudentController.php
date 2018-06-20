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
use AppBundle\Entity\Workday;
use AppBundle\Form\Model\Attendance;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
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
        $title = (string) $agreement;

        $parent = (count($users) === 1 ? 'frontpage' : 'my_student_index');

        return $this->render('student/tutor_calendar_agreement.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
                'breadcrumb' => [
                    ['fixed' => $agreement->getStudent()->getFullDisplayName(), 'path' => 'my_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                    ['fixed' => (string) $agreement->getWorkcenter()],
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
        if ($request->request->has('week_lock') || $request->request->has('week_lock_print') || ($request->request->has('week_unlock'))) {
            $this->lockWeekHelper($agreement, $request, !$request->request->has('week_unlock'));

            if ($request->request->has('week_lock_print')) {
                $data = $request->request->get('week_lock_print');
                $week = $data % 100;
                $year = intdiv($data, 100);
                return $this->redirectToRoute('my_student_weekly_report_download', ['id' => $agreement->getId(), 'week' => $week, 'year' => $year]);
            }
            $this->redirectToRoute('my_student_agreement_calendar', ['id' => $agreement->getId()]);
        }
        return $this->lockWorkdayAction($agreement, $request, $request->request->has('lock'), 'my_student_agreement_calendar');
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
        $breadcrumb = [
            ['fixed' => $agreement->getStudent()->getFullDisplayName(), 'path' => 'my_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
            ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'admin_group_student_calendar', 'options' => ['id' => $agreement->getId()]],
            ['fixed' => $this->get('translator')->trans('form.report', [], 'student')]
        ];

        $routes = ['my_student_agreement_calendar', 'my_student_agreement_report_download'];

        return $this->workTutorReportAction($agreement, $request, $breadcrumb, $routes, 'student/report_form.html.twig');
    }

    /**
     * @Route("/alumnado/seguimiento/informe/descargar/{id}", name="admin_group_agreement_report_download", methods={"GET"})
     * @Route("/estudiantes/informe/descargar/{id}", name="my_student_agreement_report_download", methods={"GET"})
     * @Security("is_granted('AGREEMENT_REPORT', agreement)")
     */
    public function downloadStudentReportAction(Agreement $agreement)
    {
        $translator = $this->get('translator');

        $title = $translator->trans('form.report', [], 'student') . ' - ' . $agreement->getStudent();

        $mpdf = $this->get('sasedev_mpdf');
        $mpdf->init();
        $this->fillWorkingTutorReport($mpdf, $translator, $agreement, $title);

        $title = str_replace(' ', '_', $title);
        return $mpdf->generateInlineFileResponse($title . '.pdf');
    }

    /**
     * @Route("/estudiantes/ficha/descargar/{id}/{year}/{week}", name="my_student_weekly_report_download", methods={"GET"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function downloadWeeklyReportAction(Agreement $agreement, $year, $week)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $isLocked = $em->getRepository('AppBundle:Workday')->isWeekLocked($agreement, $week, $year);

        if (false === $isLocked) {
            $this->denyAccessUnlessGranted('AGREEMENT_LOCK', $agreement);
        }

        $weekDays = $em->getRepository('AppBundle:Workday')->getWorkdaysInWeek($agreement, $week, $year);

        if (0 === count($weekDays)) {
            throw $this->createNotFoundException();
        }

        $weekInfo = $em->getRepository('AppBundle:Workday')->getWeekInformation($weekDays[0]);

        $translator = $this->get('translator');

        $title = $translator->trans('form.weekly_report', [], 'student') . ' - ' . $agreement->getStudent() . ' - Semana ' . str_pad($weekInfo['current'], 2, '0', STR_PAD_LEFT);

        $mpdf = $this->get('sasedev_mpdf');
        $mpdf->init();

        $this->fillWeeklyReport($mpdf, $translator, $agreement, $weekDays, $title, $isLocked, $weekInfo);

        $title = str_replace(' ', '_', $title);
        return $mpdf->generateInlineFileResponse($title . '.pdf');
    }

    /**
     * @Route("/alumnado/seguimiento/programa/descargar/{id}", name="admin_group_teaching_program_report_download", methods={"GET"})
     * @Route("/estudiantes/programa/descargar/{id}", name="my_student_teaching_program_report_download", methods={"GET"})
     * @Security("is_granted('AGREEMENT_REPORT', agreement)")
     */
    public function downloadTeachingProgramReportAction(Agreement $agreement)
    {
        $translator = $this->get('translator');

        $title = $translator->trans('form.training_program', [], 'training_program_report') . ' - ' . $agreement->getStudent() . ' - ' . $agreement->getWorkcenter();

        $mpdf = $this->get('sasedev_mpdf');
        $mpdf->init('', 'A4-L');

        $obj = $mpdf->getMpdf();
        $obj->SetImportUse();
        $obj->SetDocTemplate('pdf/Programa_Formativo_seneca_vacio.pdf', true);
        $mpdf->useTwigTemplate('student/training_program_report.html.twig', [
            'agreement' => $agreement,
            'title' => $title,
            'learning_program' => $this->getDoctrine()->getRepository('AppBundle:LearningOutcome')->getLearningProgramFromAgreement($agreement),
            'academic_year' => $this->getParameter('academic.year')
        ]);

        $title = str_replace(' ', '_', $title);
        return $mpdf->generateInlineFileResponse($title . '.pdf');
    }

    /**
     * @Route("/alumnado/seguimiento/actividades/descargar/{id}", name="admin_group_activity_report_download", methods={"GET"})
     * @Route("/estudiantes/actividades/descargar/{id}", name="my_student_activity_report_download", methods={"GET"})
     * @Security("is_granted('AGREEMENT_REPORT', agreement)")
     */
    public function downloadActivityReportAction(Agreement $agreement)
    {
        $translator = $this->get('translator');

        $title = $translator->trans('form.activity_report', [], 'activity_report') . ' - ' . $agreement->getStudent();

        $mpdf = $this->get('sasedev_mpdf');
        $mpdf->init('', 'A4');

        $obj = $mpdf->getMpdf();
        $obj->SetImportUse();
        $obj->SetDocTemplate('pdf/A4_vacio.pdf', true);

        $agreements = $agreement->getStudent()->getStudentAgreements();

        $educationalTutors = [$agreement->getEducationalTutor()];
        $totalHours = $this->getDoctrine()->getRepository('AppBundle:User')->countAgreementHours($agreement->getStudent());

        $activities = [];
        $documentDate = null;

        /** @var Agreement $a */
        foreach($agreements as $a) {
            $activities[] = [$a, $this->getDoctrine()->getRepository('AppBundle:Agreement')->getActivitiesStats($a)];
            if (null === $documentDate || $a->getToDate() > $documentDate) {
                $documentDate = $a->getToDate();
            }
        }

        $mpdf->useTwigTemplate('student/activity_report.html.twig', [
            'agreement' => $agreement,
            'title' => $title,
            'activities' => $activities,
            'educational_tutors' => $educationalTutors,
            'total_hours' => $totalHours,
            'document_date' => $documentDate,
            'academic_year' => $this->getParameter('academic.year')
        ]);

        $title = str_replace(' ', '_', $title);
        return $mpdf->generateInlineFileResponse($title . '.pdf');
    }

    /**
     * @Route("/estudiantes/detalle/{id}", name="my_student_detail", methods={"GET"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function myStudentDetailAction(Agreement $agreement)
    {
        $student = $agreement->getStudent();
        $form = $this->createForm('AppBundle\Form\Type\StudentUserType', $student, [
            'admin' => false,
            'disabled' => true
        ]);

        return $this->render('student/student_detail.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
                'breadcrumb' => [
                    ['fixed' => $agreement->getStudent()->getFullDisplayName(), 'path' => 'my_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                    ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'my_student_agreement_calendar', 'options' => ['id' => $agreement->getId()]],
                    ['fixed' => $this->get('translator')->trans('student.detail', [], 'group')]
                ],
                'title' => (string) $student,
                'user' => $student,
                'agreement' => $agreement,
                'form' => $form->createView(),
                'back' => 'my_student_agreement_calendar'
            ]);
    }

    /**
     * @Route("/estudiantes/centro/{id}", name="workcenter_detail", methods={"GET"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function workCenterDetailAction(Agreement $agreement)
    {
        $workcenter = $agreement->getWorkcenter();
        $form = $this->createForm('AppBundle\Form\Type\WorkcenterType', $workcenter, [
            'disabled' => true
        ]);
        $form2 = $this->createForm('AppBundle\Form\Type\WorkTutorType', $agreement->getWorkTutor(), [
            'disabled' => true
        ]);

        return $this->render('student/workcenter_detail.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
                'breadcrumb' => [
                    ['fixed' => $agreement->getStudent()->getFullDisplayName(), 'path' => 'my_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                    ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'my_student_agreement_calendar', 'options' => ['id' => $agreement->getId()]],
                    ['fixed' => $this->get('translator')->trans('form.workcenter', [], 'company')]
                ],
                'title' => (string) $workcenter,
                'agreement' => $agreement,
                'form' => $form->createView(),
                'form2' => $form2->createView(),
                'back' => 'my_student_agreement_calendar'
            ]);
    }

    private function attendanceForm(Request $request, Agreement $agreement, FormInterface $form)
    {
        $title = $this->get('translator')->trans('form.attendance_report', [], 'student');

        $routeName = $request->get('_route');
        if ($routeName === 'admin_group_attendance_report') {
            $breadcrumb = [
                ['fixed' => $agreement->getStudent()->getStudentGroup()->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $agreement->getStudent()->getStudentGroup()->getId()]],
                ['fixed' => (string) $agreement->getStudent(), 'path' => 'admin_group_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'admin_group_student_calendar', 'options' => ['id' => $agreement->getId()]],
                ['fixed' => $title]
            ];
            $menuItem = $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group');
            $backRoute = 'admin_group_student_calendar';
        } elseif ($routeName === 'my_student_attendance_report') {
            $breadcrumb =  [
                ['fixed' => $agreement->getStudent()->getFullDisplayName(), 'path' => 'my_student_agreements', 'options' => ['id' => $agreement->getStudent()->getId()]],
                ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'my_student_agreement_calendar', 'options' => ['id' => $agreement->getId()]],
                ['fixed' => $title]
            ];
            $menuItem = $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index');
            $backRoute = 'my_student_agreement_calendar';
        } else {
            $breadcrumb = [
                ['fixed' => (string) $agreement->getWorkcenter(), 'path' => 'student_calendar_agreement', 'options' => ['id' => $agreement->getId()]],
                ['fixed' => $title]
            ];
            $menuItem = $this->get('app.menu_builders_chain')->getMenuItemByRouteName('student_calendar');
            $backRoute = 'student_calendar_agreement';
        }
        return $this->render('student/attendance.html.twig',
            [
                'menu_item' => $menuItem,
                'breadcrumb' => $breadcrumb,
                'title' => (string) $title,
                'agreement' => $agreement,
                'form' => $form->createView(),
                'back' => $backRoute
            ]);
    }

    /**
     * @Route("/alumnado/seguimiento/asistencia/{id}", name="admin_group_attendance_report", methods={"GET"})
     * @Route("/estudiantes/asistencia/{id}", name="my_student_attendance_report", methods={"GET"})
     * @Route("/asistencia/{id}", name="attendance_report", methods={"GET"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function attendanceReportAction(Request $request, Agreement $agreement)
    {
        $form = $this->createForm('AppBundle\Form\Type\AttendanceType', new Attendance());

        return $this->attendanceForm($request, $agreement, $form);
    }

    /**
     * @Route("/alumnado/seguimiento/asistencia/{id}", name="admin_group_attendance_report_download", methods={"POST"})
     * @Route("/estudiantes/asistencia/{id}", name="my_student_attendance_report_download", methods={"POST"})
     * @Route("/asistencia/{id}", name="attendance_report_download", methods={"POST"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement)")
     */
    public function downloadAttendanceReportAction(Request $request, Agreement $agreement)
    {
        $attendance = new Attendance();
        $form = $this->createForm('AppBundle\Form\Type\AttendanceType', $attendance);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $translator = $this->get('translator');

            $title = $translator->trans('report.title', [], 'attendance_report') . ' - ' . $agreement->getStudent();

            $mpdf = $this->get('sasedev_mpdf');
            $mpdf->init('', 'A4-L');

            $obj = $mpdf->getMpdf();
            $obj->SetImportUse();
            $obj->SetDocTemplate('pdf/Modelo_de_acreditacion_de_asistencia_a_la_empresa_FCT_vacio.pdf', true);

            $mpdf->useTwigTemplate('student/attendance_report.html.twig', [
                'agreement' => $agreement,
                'attendance' => $attendance,
                'title' => $title,
                'academic_year' => $this->getParameter('academic.year')
            ]);

            $title = str_replace(' ', '_', $title);
            return $mpdf->generateInlineFileResponse($title . '.pdf');
        }
        else {
            return $this->attendanceForm($request, $agreement, $form);
        }
    }
}
