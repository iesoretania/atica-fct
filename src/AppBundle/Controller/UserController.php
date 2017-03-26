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
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/datos", name="personal_form", methods={"GET", "POST"})
     */
    public function indexAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm('AppBundle\Form\Type\UserType', $user, [
            'admin' => $this->isGranted('ROLE_ADMIN'),
            'me' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Guardar el usuario en la base de datos

            // Si es solicitado, cambiar la contraseña
            $passwordSubmit = $form->get('changePassword');
            if (($passwordSubmit instanceof SubmitButton) && $passwordSubmit->isClicked()) {
                $user->setPassword($this->get('security.password_encoder')
                    ->encodePassword($user, $form->get('newPassword')->get('first')->getData()));
                $message = $this->get('translator')->trans('alert.password_changed', [], 'user');
            } else {
                $message = $this->get('translator')->trans('alert.saved', [], 'user');
            }

            // Probar a guardar los cambios
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $message);
                return new RedirectResponse(
                    $this->generateUrl('frontpage')
                );
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'user'));
            }
        }

        return $this->render('user/form.html.twig', [
            'form' => $form->createView(),
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('personal_form'),
            'title' => null
        ]);
    }
    
    /**
     * @Route("/api/usuario/crear", name="api_user_new", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_DEPARTMENT_HEAD')")
     */
    public function apiNewUserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $newUser = new User();

        $form = $this->createForm('AppBundle\Form\Type\NewUserType', $newUser, [
            'action' => $this->generateUrl('api_user_new'),
            'method' => 'POST'
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newUser
                ->setEnabled(true)
                ->setFinancialManager(false)
                ->setGlobalAdministrator(false);

            $em->persist($newUser);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'id' => $newUser->getId(),
                'name' => $newUser->getFullPersonDisplayName()
            ]);
        }

        return $this->render('user/new_user_partial.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
