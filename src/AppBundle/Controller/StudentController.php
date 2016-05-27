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
use AppBundle\Entity\Tracking;
use AppBundle\Entity\User;
use AppBundle\Entity\Workday;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StudentController extends Controller
{
    /**
     * @Route("/grupos/estudiante/{id}", name="student_detail", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_GROUP_ADMIN', student.getStudentGroup())")
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
     * @Route("/seguimiento", name="student_calendar", methods={"GET"})
     * @Security("user.getStudentAgreements() !== null and user.getStudentAgreements().count() > 0")
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
     * @Route("/seguimiento/{id}", name="student_calendar_agreement", methods={"GET"})
     * @Security("is_granted('AGREEMENT_ACCESS', agreement) and user.getStudentAgreements() !== null and user.getStudentAgreements().count() > 0")
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

    /**
     * @Route("/seguimiento/jornada/{id}", name="student_tracking", methods={"GET", "POST"})
     * @Security("is_granted('AGREEMENT_ACCESS', workday.getAgreement()) and user.getStudentAgreements() !== null and user.getStudentAgreements().count() > 0")
     */
    public function studentWorkdayAction(Workday $workday, Request $request)
    {
        $agreementHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->countHours($workday->getAgreement());

        $dow = ((6 + (int) $workday->getDate()->format('w')) % 7);
        
        $title = $this->get('translator')->trans('dow' . $dow, [], 'calendar') . ', ' . $workday->getDate()->format('d/m/Y');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AppBundle:Tracking')->updateTrackingByWorkday($workday);

        $form = $this->createForm('AppBundle\Form\Type\WorkdayTrackingType', $workday, [
            'disabled' => $workday->isLocked()
        ]);
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
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('student_calendar'),
                'breadcrumb' => [
                    ['fixed' => (string) $workday->getAgreement()->getWorkcenter(), 'path' => 'student_calendar_agreement', 'options' => ['id' => $workday->getAgreement()->getId()]],
                    ['fixed' => $title],
                ],
                'title' => $title,
                'user' => $this->getUser(),
                'workday' => $workday,
                'form' => $form->createView(),
                'agreement_hours' => $agreementHours,
                'activities_stats' => $activitiesStats,
                'next' => $next,
                'previous' => $previous
            ]);
    }
}
