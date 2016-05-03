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

use AppBundle\Entity\Department;
use AppBundle\Form\Type\DepartmentType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/departamentos")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class AdminDepartmentController extends Controller
{
    /**
     * @Route("/", name="admin_departments", methods={"GET"})
     */
    public function departmentsIndexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $departmentsQuery = $em->createQuery('SELECT d FROM AppBundle:Department d JOIN d.head h JOIN h.person p');

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $departmentsQuery,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'd.name',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('admin/manage_departments.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_departments'),
                'title' => null,
                'pagination' => $pagination
            ]);
    }

    /**
     * @Route("/nuevo", name="admin_department_new", methods={"GET", "POST"})
     * @Route("/{department}", name="admin_department_form", methods={"GET", "POST"}, requirements={"department": "\d+"})
     */
    public function formAction(Department $department = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $new = (null === $department);
        if ($new) {
            $department = new Department();
            $em->persist($department);
        }

        $form = $this->createForm(DepartmentType::class, $department);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Probar a guardar los cambios
            try {
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'user'));
                return $this->redirectToRoute('admin_departments');
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'user'));
            }
        }

        $titulo = ((string) $department) ?: $this->get('translator')->trans('department.new', [], 'admin');

        return $this->render('admin/form_department.html.twig', [
            'form' => $form->createView(),
            'new' => $new,
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_departments'),
            'breadcrumb' => [
                ['fixed' => $titulo]
            ],
            'department' => $department,
            'title' => $titulo
        ]);
    }

    /**
     * @Route("/eliminar/{department}", name="admin_department_delete", methods={"GET", "POST"}, requirements={"department": "\d+"} )
     */
    public function deleteElementAction(Request $request, Department $department)
    {
        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            // Eliminar el departamento de la base de datos
            $this->getDoctrine()->getManager()->remove($department);
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.deleted', [], 'department'));
            }
            catch(\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], 'department'));
            }
            return $this->redirectToRoute('admin_departments');
        }

        $title = $department->getName();

        $breadcrumb = [
            ['fixed' => $title, 'path' => 'admin_department_form', 'options' => ['department' => $department->getId()]],
            ['caption' => 'menu.delete']
        ];

        return $this->render(':admin:delete_department.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_departments'),
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'department' => $department
        ]);
    }
}
