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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Security("is_granted('ROLE_TEACHING_TUTOR')")
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
        if ($this->isGranted('ROLE_DEPARTMENT_HEAD')) {
            $visits = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getEducationalTutors();
        } else {
            return $this->visitIndexAction($user);
        }

        return $this->render('visit/index.html.twig', [
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
        $visits = $this->getDoctrine()->getManager()->getRepository('AppBundle:Workcenter')->getVisitRelatedWorkcenters($tutor);

        $title = (string) $tutor;

        return $this->render('visit/workcenter_index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('visit_index'),
            'breadcrumb' => [
                ['fixed' => $title]
            ],
            'title' => $this->get('translator')->trans('browse.workcenter', ['%user%' => $title], 'visit'),
            'elements' => $visits,
            'back_route_name' => $this->isGranted('ROLE_DEPARTMENT_HEAD') ? 'visit_index' : 'frontpage'
        ]);
    }
}
