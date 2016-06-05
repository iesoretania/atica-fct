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

use AppBundle\Entity\User;
use AppBundle\Entity\Visit;
use AppBundle\Entity\Workcenter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_EDUCATIONAL_TUTOR')")
 */
class VisitController extends Controller
{
    /**
     * @Route("/visitas", name="visit_index", methods={"GET"})
     */
    public function teacherIndexAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $visits = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getEducationalTutorsAgreementSummary();
        } elseif ($this->isGranted('ROLE_DEPARTMENT_HEAD')) {
            $visits = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getEducationalTutorsByDepartmentsAgreementSummary($user->getDirects());
        } else {
            return $this->visitIndexAction($user);
        }

        return $this->render('visit/tutor_index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('visit_index'),
            'title' => null,
            'elements' => $visits
        ]);
    }

    /**
     * @Route("/visitas/{id}", name="visit_workcenter_index", methods={"GET"})
     * @Security("is_granted('USER_VISIT_TRACK', tutor)")
     */
    public function visitIndexAction(User $tutor)
    {
        $workcenters = $this->getDoctrine()->getManager()->getRepository('AppBundle:Visit')->getRelatedVisits($tutor);

        $title = (string) $tutor;

        return $this->render('visit/visit_index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('visit_index'),
            'breadcrumb' => [
                ['fixed' => $title]
            ],
            'title' => $this->get('translator')->trans('browse.workcenter', ['%user%' => $title], 'visit'),
            'tutor' => $tutor,
            'elements' => $workcenters,
            'back_route_name' => $this->isGranted('ROLE_DEPARTMENT_HEAD') ? 'visit_index' : 'frontpage'
        ]);
    }

    /**
     * @Route("/visitas/{id}/resumen", name="visit_workcenter_summary_index", methods={"GET"})
     * @Security("is_granted('USER_VISIT_TRACK', tutor)")
     */
    public function visitWorkcenterSummaryAction(User $tutor)
    {
        $workcenters = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workcenter')->getVisitRelatedWorkcenters($tutor);

        $title = (string) $tutor;

        return $this->render('visit/workcenter_summary.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('visit_index'),
            'breadcrumb' => [
                ['fixed' => $title, 'path' => 'visit_workcenter_index', 'options' => ['id' => $tutor->getId()]],
                ['fixed' => $this->get('translator')->trans('browse.summary', [], 'visit')]
            ],
            'title' => $this->get('translator')->trans('browse.workcenter', ['%user%' => $title], 'visit'),
            'tutor' => $tutor,
            'elements' => $workcenters,
            'back_route_name' => $this->isGranted('ROLE_DEPARTMENT_HEAD') ? 'visit_index' : 'frontpage'
        ]);
    }
    
    /**
     * @Route("/visitas/{id}/modificar/{visit}", name="visit_form", methods={"GET", "POST"})
     * @Security("is_granted('USER_VISIT_TRACK', tutor) and visit.getTutor() == tutor")
     */
    public function visitFormAction(User $tutor, Visit $visit, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\VisitType', $visit);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->getDoctrine()->getManager()->persist($visit);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'visit'));
            return $this->redirectToRoute('visit_workcenter_index', ['id' => $tutor->getId()]);
        }
        $title = $this->get('translator')->trans($visit->getId() ? 'form.view' : 'form.new', [], 'visit');

        return $this->render('visit/form_visit.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('visit_index'),
            'breadcrumb' => [
                ['fixed' => (string) $tutor, 'path' => 'visit_workcenter_index', 'options' => ['id' => $tutor->getId()]],
                ['fixed' => $title]
            ],
            'title' => $title,
            'visit' => $visit,
            'form' => $form->createView(),
            'tutor' => $tutor
        ]);
    }

    /**
     * @Route("/visitas/{id}/registrar", name="visit_form_new", methods={"GET", "POST"})
     * @Security("is_granted('USER_VISIT_TRACK', tutor)")
     */
    public function visitNewAction(User $tutor, Request $request)
    {
        $visit = new Visit();
        $visit->setTutor($tutor);
        $visit->setDate(new \DateTime());

        return $this->visitFormAction($tutor, $visit, $request);
    }
}
