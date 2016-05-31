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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    public function baseWorkdayAction(Workday $workday, Request $request, $options)
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

    public function studentListIndexAction(Query $query, Request $request, $options)
    {
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'u.lastName',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render($options['template'],
            [
                'menu_item' => $options['menu_item'],
                'breadcrumb' => $options['breadcrumb'],
                'title' => $options['title'],
                'pagination' => $pagination,
                'user' => $this->getUser()
            ]);
    }
}
