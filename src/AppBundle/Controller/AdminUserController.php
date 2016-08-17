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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/usuarios")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminUserController extends Controller
{
    /**
     * @Route("/", name="admin_users", methods={"GET"})
     */
    public function usersIndexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $usersQuery = $em->createQuery('SELECT u FROM AppBundle:User u');

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'u.lastName',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('admin/manage_users.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_users'),
                'title' => null,
                'pagination' => $pagination
            ]);
    }

    /**
     * @Route("/nuevo", name="admin_user_new", methods={"GET", "POST"})
     * @Route("/{user}", name="admin_user_form", methods={"GET", "POST"}, requirements={"user": "\d+"})
     */
    public function formAction(User $user = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $new = (null === $user);
        if ($new) {
            $user = $em->getRepository('AppBundle:User')->createNewUser();
            $user->setEnabled(true);
        }

        $me = ($user->getId() === $this->getUser()->getId());

        $form = $this->createForm('AppBundle\Form\Type\UserType', $user, [
            'admin' => $this->isGranted('ROLE_ADMIN'),
            'me' => $me,
            'new' => $new
        ]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Guardar el usuario en la base de datos

            // Si es solicitado, cambiar la contraseña
            $passwordSubmit = $form->get('changePassword');
            if (($passwordSubmit instanceof SubmitButton) && $passwordSubmit->isClicked()) {
                $password = $this->container->get('security.password_encoder')
                    ->encodePassword($user, $form->get('newPassword')->get('first')->getData());
                $user->setPassword($password);
                $message = $this->get('translator')->trans('alert.password_changed', [], 'user');
            } else {
                $message = $this->get('translator')->trans('alert.saved', [], 'user');
            }

            // Probar a guardar los cambios
            try {
                $em->flush();
                $this->addFlash('success', $message);
                return $this->redirectToRoute('admin_users');
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'user'));
            }
        }

        $titulo = ((string) $user) ?: $this->get('translator')->trans('user.new', [], 'user');

        return $this->render('admin/form_user.html.twig', [
            'form' => $form->createView(),
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_users'),
            'breadcrumb' => [
                ['fixed' => $titulo]
            ],
            'title' => $titulo,
            'user' => $user
        ]);
    }

    /**
     * @Route("/eliminar/{id}", name="admin_user_delete", methods={"GET", "POST"}, requirements={"user": "\d+"})
     */
    public function genericDeleteAction(User $element, Request $request)
    {
        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            // Eliminar el departamento de la base de datos
            $this->getDoctrine()->getManager()->remove($element);
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.deleted', [], 'user'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], 'user'));
            }
            return $this->redirectToRoute('admin_users');
        }

        $title = (string) $element;

        $breadcrumb = [
            ['fixed' => $title],
            ['caption' => 'menu.delete']
        ];

        return $this->render('admin/delete_user.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_users'),
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'element' => $element,
        ]);
    }
}
