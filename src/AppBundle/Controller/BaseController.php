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
use AppBundle\Entity\Tracking;
use AppBundle\Entity\Workday;
use Sasedev\MpdfBundle\Service\MpdfService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class BaseController extends Controller
{
    protected function baseWorkdayAction(Workday $workday, Request $request, $options)
    {
        $agreement = $workday->getAgreement();
        $agreementHours = $this->getDoctrine()->getManager()->getRepository('AppBundle:Agreement')->countHours($agreement);

        $dow = ((6 + (int) $workday->getDate()->format('w')) % 7);

        $title = $this->get('translator')->trans('dow' . $dow, [], 'calendar') . ', ' . $workday->getDate()->format('d/m/Y');
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('AppBundle:Tracking')->updateTrackingByWorkday($workday);

        $form = $this->createForm('AppBundle\Form\Type\WorkdayTrackingType', $workday, [
            'disabled' => (!$this->isGranted('AGREEMENT_FILL', $agreement) || $workday->isLocked()) && (!$this->isGranted('AGREEMENT_UNLOCK', $agreement))
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

        $activitiesStats = $em->getRepository('AppBundle:Agreement')->getActivitiesStats($agreement);
        $next = $em->getRepository('AppBundle:Workday')->getNext($workday);
        $previous = $em->getRepository('AppBundle:Workday')->getPrevious($workday);

        return $this->render('student/tracking_form.html.twig',
            [
                'menu_item' => $options['menu_item'],
                'breadcrumb' => $options['breadcrumb'],
                'title' => $title,
                'user' => $this->getUser(),
                'workday' => $workday,
                'form' => $form->createView(),
                'agreement_hours' => $agreementHours,
                'activities_stats' => $activitiesStats,
                'next' => $next,
                'previous' => $previous,
                'back_route_name' => $options['back_route_name']
            ]);
    }

    protected function lockWorkdayAction(Agreement $agreement, Request $request, $status, $routeName)
    {
        $this->denyAccessUnlessGranted($status ? 'AGREEMENT_LOCK' : 'AGREEMENT_UNLOCK', $agreement);

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
        return $this->redirectToRoute($routeName, ['id' => $agreement->getId()]);
    }

    protected function lockWeekAction(Agreement $agreement, Request $request, $status, $routeName)
    {
        $this->denyAccessUnlessGranted($status ? 'AGREEMENT_LOCK' : 'AGREEMENT_UNLOCK', $agreement);

        $data = $request->get($status ? 'week_lock' : 'week_unlock');
        $week = $data % 100;
        $year = intdiv($data, 100);

        $em = $this->getDoctrine()->getManager();

        $workdays = $em->getRepository('AppBundle:Workday')->getWorkdaysInWeek($agreement, $week, $year);

        try {
            /** @var Workday $workday */
            foreach ($workdays as $workday) {
                $workday->setLocked($status);
            }

            $em->flush();
            $this->addFlash('success', $this->get('translator')->trans('alert.locked', [], 'calendar'));
        } catch (\Exception $e) {
            $this->addFlash('error', $this->get('translator')->trans('alert.locked_error', [], 'calendar'));
        }

        return $this->redirectToRoute($routeName, ['id' => $agreement->getId()]);
    }

    protected function deleteWorkdayAction(Agreement $agreement, Request $request)
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
                    if ($date->getTrackedHours() === 0.0) {
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

    protected function workTutorReportAction(Agreement $agreement, Request $request, $breadcrumb, $routes, $template)
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
                $request->request->has('submit') ? $routes[0] : $routes[1],
                ['id' => $agreement->getId()]);
        }

        return $this->render($template, [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
            'breadcrumb' => $breadcrumb,
            'form' => $form->createView(),
            'agreement' => $agreement
        ]);
    }

    /**
     * @param MpdfService $mpdf
     * @param $text
     * @param $x
     * @param $y
     * @param $w
     * @param $h
     * @param string $overflow
     * @param string $align
     * @param bool $escape
     */
    private function pdfWriteFixedPosHTML(MpdfService $mpdf, $text, $x, $y, $w, $h, $overflow = 'auto', $align = 'left', $escape = true)
    {
        $obj = $mpdf->getMpdf();

        if ($escape) {
            $text = nl2br(htmlentities($text));
        }
        $obj->WriteFixedPosHTML('<div style="font-family: sans-serif; font-size: 12px; text-align: ' . $align . ';">' . $text . '</div>', $x, $y, $w, $h, $overflow);
    }

    /**
     * @param MpdfService $mpdf
     * @param TranslatorInterface $translator
     * @param Agreement $agreement
     * @param string $title
     */
    protected function fillWorkingTutorReport(MpdfService $mpdf, TranslatorInterface $translator, Agreement $agreement, $title)
    {
        $obj = $mpdf->getMpdf();
        $obj->SetImportUse();
        $obj->SetDocTemplate('pdf/Informe_tutor_laboral_seneca_rellenable.pdf');

        $obj->SetFont('DejaVuSansCondensed');
        $obj->SetFontSize(9);
        $obj->SetTitle($title);

        $obj->AddPage();
        $obj->WriteText(40, 40.8, (string) $agreement->getStudent());
        $obj->WriteText(40, 46.6, $this->getParameter('organization.name'));
        $obj->WriteText(40, 53, (string) $agreement->getStudent()->getStudentGroup()->getTraining());
        $obj->WriteText(179, 53, (string) $agreement->getStudent()->getStudentGroup()->getTraining()->getStage());
        $obj->WriteText(40, 59.1, (string) $agreement->getWorkcenter());
        $obj->WriteText(165, 59.1, (string) $agreement->getTrackedHours());
        $obj->WriteText(82, 65.1, (string) $agreement->getWorkTutor());
        $obj->WriteText(68, 71.5, (string) $agreement->getEducationalTutor());

        $obj->WriteText(108 + $agreement->getReport()->getProfessionalCompetence() * 35.0, 137, 'X');
        $obj->WriteText(108 + $agreement->getReport()->getOrganizationalCompetence() * 35.0, 143.5, 'X');
        $obj->WriteText(108 + $agreement->getReport()->getRelationalCompetence() * 35.0, 149.5, 'X');
        $obj->WriteText(108 + $agreement->getReport()->getContingencyResponse() * 35.0, 155.5, 'X');

        $obj->WriteText(104.6, 247.6, $agreement->getReport()->getSignDate()->format('d'));
        $obj->WriteText(154.4, 247.6, $agreement->getReport()->getSignDate()->format('y'));
        $obj->WriteText(89, 275.6, (string) $agreement->getWorkTutor());

        $this->pdfWriteFixedPosHTML($mpdf, $agreement->getWorkcenter()->getCity(), 61, 244.4, 38, 5, 'auto', 'center');
        $this->pdfWriteFixedPosHTML($mpdf, $translator->trans('r_month' . ($agreement->getReport()->getSignDate()->format('n') - 1), [], 'calendar'), 116, 244.4, 26, 5, 'auto', 'center');
        $this->pdfWriteFixedPosHTML($mpdf, $agreement->getReport()->getWorkActivities(), 18, 80, 179, 40.5, 'auto', 'justify');
        $this->pdfWriteFixedPosHTML($mpdf, $agreement->getReport()->getProposedChanges(), 18, 195, 179, 43, 'auto', 'justify');
    }

    /**
     * @param MpdfService $mpdf
     * @param TranslatorInterface $translator
     * @param Agreement $agreement
     * @param Workday[] $weekDays
     * @param string $title
     * @param boolean $isLocked
     * @param array $weekCounter
     */
    protected function fillWeeklyReport(MpdfService $mpdf, TranslatorInterface $translator, Agreement $agreement, $weekDays, $title, $isLocked, $weekCounter)
    {
        $activities = [];
        $hours = [];
        $notes = [];
        $noActivity = htmlentities($translator->trans('form.no_activities', [], 'calendar'));
        $noWorkday = htmlentities($translator->trans('form.no_workday', [], 'calendar'));

        foreach($weekDays as $workDay) {
            $day = $workDay->getDate()->format('N');
            $activities[$day] = '';
            $hours[$day] = '';

            foreach($workDay->getTrackingActivities() as $trackingActivity) {
                $activities[$day] .= '<li style="list-style-position: inside; list-style: square;">';
                if ($trackingActivity->getActivity()->getCode()) {
                    $activities[$day] .= '<b>' . htmlentities($trackingActivity->getActivity()->getCode()) . ': </b>';
                }
                $activities[$day] .= htmlentities($trackingActivity->getActivity()->getName()) . '</li>';
                $hours[$day] .= '<li style="list-style-position: inside; list-style: square;">' . $translator->transChoice('form.r_hours', $trackingActivity->getHours(), ['%hours%' => $trackingActivity->getHours()], 'calendar') . '</li>';
            }

            if ('' === $activities[$day]) {
                $activities[$day] = '<i>' . $noActivity . '</i>';
            }
            $notes[$day] = $workDay->getNotes();
        }

        $obj = $mpdf->getMpdf();

        $obj->SetImportUse();
        $obj->SetDocTemplate('pdf/Ficha_semanal_alumno_seneca_rellenable.pdf');

        $obj->SetTitle($title);

        $obj->AddPage('L');

        // añadir fecha a la ficha
        $first = end($weekDays);
        $last = reset($weekDays);

        $this->pdfWriteFixedPosHTML($mpdf, $first->getDate()->format('j'), 54.5, 33.5, 8, 5, 'auto', 'center');
        $this->pdfWriteFixedPosHTML($mpdf, $last->getDate()->format('j'), 67.5, 33.5, 10, 5, 'auto', 'center');
        $this->pdfWriteFixedPosHTML($mpdf, $translator->trans('r_month' . ($last->getDate()->format('n') - 1), [], 'calendar'), 85, 33.5, 23.6, 5, 'auto', 'center');
        $this->pdfWriteFixedPosHTML($mpdf, $last->getDate()->format('y'), 118.5, 33.5, 6, 5, 'auto', 'center');

        // añadir números de página
        $this->pdfWriteFixedPosHTML($mpdf, $weekCounter['current'], 245.5, 21.9, 6, 5, 'auto', 'center');
        $this->pdfWriteFixedPosHTML($mpdf, $weekCounter['total'], 254.8, 21.9, 6, 5, 'auto', 'center');

        // añadir campos de la cabecera
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getWorkcenter(), 192, 40.8, 72, 5, 'auto', 'left');
        $this->pdfWriteFixedPosHTML($mpdf, $this->getParameter('organization.name'), 62.7, 40.9, 80, 5, 'auto', 'left');
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getEducationalTutor(), 97.5, 46.5, 46, 5, 'auto', 'left');
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getWorkTutor(), 198, 46.5, 66, 5, 'auto', 'left');
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getStudent()->getStudentGroup()->getTraining(), 172, 54, 61, 5, 'auto', 'left');
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getStudent()->getStudentGroup()->getTraining()->getStage(), 244, 54, 20, 5, 'auto', 'left');
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getStudent(), 63, 54, 80, 5, 'auto', 'left');

        // añadir actividades semanales
        for ($n = 1; $n < 6; $n++) {
            if (isset($activities[$n])) {
                $activity = $activities[$n];
                $hour = $hours[$n];
                $note = $notes[$n];
            } else {
                $activity = '<i>' . $noWorkday . '</i>';
                $hour = '';
                $note = '';
            }
            $this->pdfWriteFixedPosHTML($mpdf, $activity, 58, 73.0 + ($n - 1) * 17.8, 128, 15.8, 'auto', 'left', false);
            $this->pdfWriteFixedPosHTML($mpdf, $hour, 189, 73.0 + ($n - 1) * 17.8, 25, 15.8, 'auto', 'left', false);
            $this->pdfWriteFixedPosHTML($mpdf, $note, 217.5, 73.0 + ($n - 1) * 17.8, 46, 15.8, 'auto', 'justify', true);
        }

        // añadir pie de firmas
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getStudent(), 68, 185.4, 53, 5, 'auto', 'left');
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getEducationalTutor(), 136, 186.9, 53, 5, 'auto', 'left');
        $this->pdfWriteFixedPosHTML($mpdf, (string) $agreement->getWorkTutor(), 204, 184.9, 53, 5, 'auto', 'left');

        // si no está bloqueada la semana, agregar la marca de agua de borrador
        if (!$isLocked) {
            $obj->SetWatermarkText($translator->trans('form.draft', [], 'calendar'), 0.1);
            $obj->showWatermarkText = true;
            $obj->watermark_font = 'DejaVuSansCondensed';
        }
    }
}
