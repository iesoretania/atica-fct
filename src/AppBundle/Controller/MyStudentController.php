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
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

class MyStudentController extends BaseController
{
    /**
     * @Route("/estudiantes", name="my_student_index", methods={"GET"})
     */
    public function myStudentIndexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:User')->getAgreementRelatedStudents($this->getUser());

        return $this->render('student/index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
            'title' => null,
            'elements' => $users
        ]);
    }


    /**
     * @Route("/estudiantes/{id}", name="my_student_agreements", methods={"GET"})
     * @Security("is_granted('USER_TRACK', user)")
     */
    public function myStudentAgreementIndexAction(User $user)
    {
        $agreements = $user->getStudentAgreements();

        /*if (count($agreements) == 1) {
            return $this->studentCalendarAgreementIndexAction($agreements[0]);
        }*/

        $title = $user->getFullDisplayName();
        return $this->render('student/calendar_agreement_select.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('my_student_index'),
                'breadcrumb' => [
                    ['fixed' => $title]
                ],
                'student' => $user,
                'title' => $title,
                'elements' => $agreements
            ]);
    }
}
