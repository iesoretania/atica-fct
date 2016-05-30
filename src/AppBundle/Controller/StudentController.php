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
use AppBundle\Entity\Workday;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class StudentController extends BaseController
{
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
        return $this->baseWorkdayAction($workday, $request, [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('student_calendar'),
            'breadcrumb' => [
                ['fixed' => (string) $workday->getAgreement()->getWorkcenter(), 'path' => 'student_calendar_agreement', 'options' => ['id' => $workday->getAgreement()->getId()]],
                ['fixed' => $workday->getDate()->format('d/m/Y')]
            ],
            'back_route_name' => 'student_calendar_agreement'
        ]);
    }
}
