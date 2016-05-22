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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/estudiante/{id}")
 * @Security("is_granted('ROLE_TUTOR')")
 */
class StudentController extends Controller
{
    /**
     * @Route("", name="student_detail", methods={"GET", "POST"})
     */
    public function studentIndexAction(User $user, Request $request)
    {
        // Si el alumno no es un estudiante, denegar acceso
        if (null === $user->getStudentGroup()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm('AppBundle\Form\Type\StudentUserType', $user, [
            'admin' => !$this->isGranted('ROLE_ADMIN')
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Guardar el usuario en la base de datos

            // Probar a guardar los cambios
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'student'));
                return $this->redirectToRoute('group_students', ['id' => $user->getStudentGroup()->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'student'));
            }
        }
        return $this->render('group/form_student.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('frontpage'),
                'breadcrumb' => [
                    ['fixed' => $user->getStudentGroup()->getName(), 'path' => 'group_students', 'options' => ['id' => $user->getStudentGroup()->getId()]],
                    ['fixed' => (string) $user],
                ],
                'title' => (string) $user,
                'user' => $user,
                'form' => $form->createView()
            ]);
    }
}
