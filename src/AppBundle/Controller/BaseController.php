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
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
}
