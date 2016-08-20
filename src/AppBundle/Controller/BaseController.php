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
use AppBundle\Entity\Workday;
use Sasedev\MpdfBundle\Service\MpdfService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;

class BaseController extends Controller
{
    public function baseWorkdayAction(Workday $workday, Request $request, $options)
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


    public function lockWorkdayAction(Agreement $agreement, Request $request, $status, $routeName)
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

    public function lockWeekAction(Agreement $agreement, Request $request, $status, $routeName)
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


    /**
     * @param MpdfService $mpdf
     * @param TranslatorInterface $translator
     * @param Agreement $agreement
     * @param string $title
     */
    public function fillReport(MpdfService $mpdf, TranslatorInterface $translator, Agreement $agreement, $title)
    {
        $obj = $mpdf->getMpdf();
        $obj->SetTitle($title);
        $obj->SetImportUse();
        $obj->SetDocTemplate('pdf/Informe_tutor_laboral_seneca_rellenable.pdf');
        $obj->AddPage();
        $obj->SetFont('DejaVuSansCondensed');
        $obj->SetFontSize(9);
        $obj->WriteText(40, 40.8, (string) $agreement->getStudent());
        $obj->WriteText(40, 46.6, $this->getParameter('organization.name'));
        $obj->WriteText(40, 53, (string) $agreement->getStudent()->getStudentGroup()->getTraining());
        $obj->WriteText(179, 53, (string) $agreement->getStudent()->getStudentGroup()->getTraining()->getStage());
        $obj->WriteText(40, 59.1, (string) $agreement->getWorkcenter());
        $obj->WriteText(165, 59.1, (string) $agreement->getTrackedHours());
        $obj->WriteText(82, 65.1, (string) $agreement->getWorkTutor());
        $obj->WriteText(68, 71.6, (string) $agreement->getEducationalTutor());

        $obj->WriteText(108 + $agreement->getReport()->getProfessionalCompetence() * 35.0, 137, 'X');
        $obj->WriteText(108 + $agreement->getReport()->getOrganizationalCompetence() * 35.0, 143.5, 'X');
        $obj->WriteText(108 + $agreement->getReport()->getRelationalCompetence() * 35.0, 149.5, 'X');
        $obj->WriteText(108 + $agreement->getReport()->getContingencyResponse() * 35.0, 155.5, 'X');

        $obj->WriteFixedPosHTML('<div style="font-family: sans-serif; font-size: 12px;">' . nl2br(htmlentities($agreement->getReport()->getWorkActivities())) . '</div>', 18, 80, 179, 41.6, 'auto');
        $obj->WriteFixedPosHTML('<div style="font-family: sans-serif; font-size: 12px;">' . nl2br(htmlentities($agreement->getReport()->getProposedChanges())) . '</div>', 18, 195, 179, 43, 'auto');

        $obj->WriteText(61, 247.6, $agreement->getWorkcenter()->getCity());
        $obj->WriteText(104.5, 247.6, $agreement->getReport()->getSignDate()->format('d'));
        $obj->WriteText(116, 247.6, $translator->trans('month' . ($agreement->getReport()->getSignDate()->format('n') - 1), [], 'calendar'));
        $obj->WriteText(153, 247.6, $agreement->getReport()->getSignDate()->format('y'));
        $obj->WriteText(89, 275.6, (string) $agreement->getWorkTutor());
    }
}
