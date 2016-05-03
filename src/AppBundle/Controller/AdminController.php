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
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    public static $DEPARTMENT_ENTITY_DATA = [
            'entity' => 'department',
            'entityClassName' => 'AppBundle\Entity\Department',
            'entityFormType' => 'AppBundle\Form\Type\DepartmentType',
            'query' => 'SELECT d FROM AppBundle:Department d JOIN d.head h JOIN h.person p',
            'defaultSortFieldName' => 'd.name',
            'columns' => [
                ['size' => '5', 'sort_field' => 'd.name', 'name' => 'department.name'],
                ['size' => '4', 'sort_field' => 'p.displayName', 'name' => 'department.head'],
            ],
            'data_columns' => ['name', 'head']
        ];

    public static $COMPANY_ENTITY_DATA = [
        'entity' => 'company',
        'entityClassName' => 'AppBundle\Entity\Company',
        'entityFormType' => 'AppBundle\Form\Type\CompanyType',
        'query' => 'SELECT c FROM AppBundle:Company c',
        'defaultSortFieldName' => 'c.name',
        'columns' => [
            ['size' => '2', 'sort_field' => 'c.code', 'name' => 'company.code'],
            ['size' => '7', 'sort_field' => 'c.name', 'name' => 'company.name'],
        ],
        'data_columns' => ['code', 'name']
    ];

    /**
     * @Route("/", name="admin_menu", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $menuItem = $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_menu');
        
        return $this->render('admin/menu.html.twig',
            [
                'menu_item' => $menuItem
            ]);
    }

    public function genericIndexAction($entityData, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery($entityData['query']);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => $entityData['defaultSortFieldName'],
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('admin/manage_generic.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName($request->get('_route')),
                'title' => null,
                'pagination' => $pagination,
                'entity' => $entityData['entity'],
                'columns' => $entityData['columns'],
                'data_columns' => $entityData['data_columns']
            ]);
    }

    public function genericFormAction($entityData, $element, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $new = (null === $element);
        if ($new) {
            $element = new $entityData['entityClassName'];
            $em->persist($element);
        }

        $form = $this->createForm($entityData['entityFormType'], $element);

        $form->handleRequest($request);

        $menuItem = $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_' . $entityData['entity']);

        if ($form->isSubmitted() && $form->isValid()) {

            // Probar a guardar los cambios
            try {
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'user'));
                return $this->redirectToRoute($menuItem->getRouteName(), $menuItem->getRouteParams());
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'user'));
            }
        }

        $title = ((string) $element) ?: $this->get('translator')->trans('form.new', [], $entityData['entity']);

        return $this->render('admin/form_generic.html.twig', [
            'form' => $form->createView(),
            'new' => $new,
            'menu_item' => $menuItem,
            'breadcrumb' => [
                ['fixed' => $title]
            ],
            'element' => $element,
            'title' => $title,
            'entity' => $entityData['entity']
        ]);
    }

    public function genericDeleteAction($entityData, $element, Request $request)
    {
        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            // Eliminar el departamento de la base de datos
            $this->getDoctrine()->getManager()->remove($element);
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.deleted', [], $entityData['entity']));
            }
            catch(\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], $entityData['entity']));
            }
            return $this->redirectToRoute('admin_' . $entityData['entity']);
        }

        $title = (string) $element;

        $breadcrumb = [
            ['fixed' => $title, 'path' => 'admin_' . $entityData['entity'] . '_form', 'options' => ['id' => $element->getId()]],
            ['caption' => 'menu.delete']
        ];

        return $this->render(':admin:delete_generic.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_departments'),
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'element' => $element,
            'entity' => $entityData['entity']
        ]);
    }

    /**
     * @Route("/departamentos", name="admin_department", methods={"GET"})
     */
    public function departmentsIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$DEPARTMENT_ENTITY_DATA, $request);
    }

    /**
     * @Route("/departamentos/nuevo", name="admin_department_new", methods={"GET", "POST"})
     * @Route("/departamentos/{id}", name="admin_department_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function departmentsFormAction(Department $element = null, Request $request)
    {
        return $this->genericFormAction(self::$DEPARTMENT_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/departamentos/eliminar/{id}", name="admin_department_delete", methods={"GET", "POST"}, requirements={"id": "\d+"} )
     */
    public function deleteElementAction(Department $element, Request $request)
    {
        return $this->genericDeleteAction(self::$DEPARTMENT_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/empresas", name="admin_company", methods={"GET"})
     */
    public function companiesIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$COMPANY_ENTITY_DATA, $request);
    }

    /**
     * @Route("/empresas/nueva", name="admin_company_new", methods={"GET", "POST"})
     * @Route("/empresas/{id}", name="admin_company_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function companyFormAction(Department $element = null, Request $request)
    {
        return $this->genericFormAction(self::$COMPANY_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/empresas/eliminar/{id}", name="admin_company_delete", methods={"GET", "POST"}, requirements={"id": "\d+"} )
     */
    public function companyDeleteAction(Department $element, Request $request)
    {
        return $this->genericDeleteAction(self::$COMPANY_ENTITY_DATA, $element, $request);
    }
}
