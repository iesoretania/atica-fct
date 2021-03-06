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
use AppBundle\Entity\Group;
use AppBundle\Entity\NonSchoolDay;
use AppBundle\Entity\Training;
use AppBundle\Entity\Workcenter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    public static $DEPARTMENT_ENTITY_DATA = [
            'entity' => 'department',
            'entityClassName' => 'AppBundle\Entity\Department',
            'entityFormType' => 'AppBundle\Form\Type\DepartmentType',
            'query' => 'SELECT d FROM AppBundle:Department d LEFT JOIN d.head h',
            'defaultSortFieldName' => 'd.name',
            'columns' => [
                ['size' => '5', 'sort_field' => 'd.name', 'name' => 'department.name'],
                ['size' => '4', 'sort_field' => 'h.displayName', 'name' => 'department.head'],
            ],
            'data_columns' => ['name', 'head']
        ];

    public static $GROUP_ENTITY_DATA = [
        'entity' => 'group',
        'entityClassName' => 'AppBundle\Entity\Group',
        'entityFormType' => 'AppBundle\Form\Type\GroupType',
        'query' => 'SELECT g FROM AppBundle:Group g JOIN g.training t',
        'defaultSortFieldName' => 'g.name',
        'columns' => [
            ['size' => '4', 'sort_field' => 'g.name', 'name' => 'form.name'],
            ['size' => '5', 'sort_field' => 'g.training', 'name' => 'form.training']
        ],
        'data_columns' => ['name', 'training']
    ];

    public static $TRAINING_ENTITY_DATA = [
        'entity' => 'training',
        'entityClassName' => 'AppBundle\Entity\Training',
        'entityFormType' => 'AppBundle\Form\Type\TrainingType',
        'query' => 'SELECT t FROM AppBundle:Training t JOIN t.department d',
        'defaultSortFieldName' => 't.name',
        'columns' => [
            ['size' => '4', 'sort_field' => 't.name', 'name' => 'form.name'],
            ['size' => '3', 'sort_field' => 'd.name', 'name' => 'form.department'],
            ['size' => '2', 'sort_field' => 't.programHours', 'name' => 'form.program_hours']
        ],
        'data_columns' => ['name', 'department', 'programHours']
    ];

    public static $NON_SCHOOL_DAY_ENTITY_DATA = [
        'entity' => 'non_school_day',
        'entityClassName' => 'AppBundle\Entity\NonSchoolDay',
        'entityFormType' => 'AppBundle\Form\Type\NonSchoolDayType',
        'query' => 'SELECT n FROM AppBundle:NonSchoolDay n',
        'defaultSortFieldName' => 'n.date',
        'columns' => [
            ['size' => '2', 'sort_field' => 'n.date', 'name' => 'form.date'],
            ['size' => '7', 'sort_field' => 'n.name', 'name' => 'form.name']
        ],
        'data_columns' => ['date', 'name']
    ];

    /**
     * @Route("/admin", name="admin_menu", methods={"GET"})
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

    public function genericIndexAction($entityData, Request $request, $items = null, Query $query = null)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if (null === $query) {
            $query = $em->createQuery($entityData['query']);

            if (null !== $items) {
                $query->setParameters($items);
            }
        }

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

        $menuItem = $this->get('app.menu_builders_chain')->getMenuItemByRouteName(isset($entityData['parent'])
            ? $entityData['parent']
            : $request->get('_route'));

        $options = [
            'menu_item' => $menuItem,
            'title' => isset($entityData['title']) ? $entityData['title'] : null,
            'pagination' => $pagination,
            'entity' => $entityData['entity'],
            'columns' => $entityData['columns'],
            'data_columns' => $entityData['data_columns']
        ];

        if (isset($entityData['breadcrumb'])) {
            $options['breadcrumb'] = $entityData['breadcrumb'];
        }

        if (isset($entityData['back_path'])) {
            $options['back_path'] = $entityData['back_path'];
        }

        if (isset($entityData['path_options'])) {
            $options['path_options'] = $entityData['path_options'];
        }

        return $this->render(isset($entityData['manage_template']) ? $entityData['manage_template']
            : 'admin/manage_generic.html.twig', $options);
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

        return $this->render(isset($entityData['form_template']) ? $entityData['form_template'] : 'admin/form_generic.html.twig', [
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
            } catch (\Exception $e) {
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
     * @Route("/admin/departamentos", name="admin_department", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function departmentsIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$DEPARTMENT_ENTITY_DATA, $request);
    }

    /**
     * @Route("/admin/departamentos/nuevo", name="admin_department_new", methods={"GET", "POST"})
     * @Route("/admin/departamentos/{id}", name="admin_department_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function departmentsFormAction(Department $element = null, Request $request)
    {
        return $this->genericFormAction(self::$DEPARTMENT_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/admin/departamentos/eliminar/{id}", name="admin_department_delete", methods={"GET", "POST"}, requirements={"id": "\d+"} )
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteElementAction(Department $element, Request $request)
    {
        return $this->genericDeleteAction(self::$DEPARTMENT_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/admin/grupos", name="admin_group", methods={"GET"})
     * @Security("is_granted('ROLE_DEPARTMENT_HEAD')")
     */
    public function groupIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$GROUP_ENTITY_DATA, $request);
    }

    /**
     * @Route("/admin/grupos/nuevo", name="admin_group_new", methods={"GET", "POST"})
     * @Route("/admin/grupos/{id}", name="admin_group_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function groupFormAction(Group $element = null, Request $request)
    {
        return $this->genericFormAction(self::$GROUP_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/admin/grupos/eliminar/{id}", name="admin_group_delete", methods={"GET", "POST"}, requirements={"id": "\d+"} )
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function groupDeleteAction(Group $element, Request $request)
    {
        return $this->genericDeleteAction(self::$GROUP_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/admin/ensenanzas", name="admin_training", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function trainingIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$TRAINING_ENTITY_DATA, $request);
    }

    /**
     * @Route("/admin/ensenanzas/nueva", name="admin_training_new", methods={"GET", "POST"})
     * @Route("/admin/ensenanzas/{id}", name="admin_training_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function trainingFormAction(Training $element = null, Request $request)
    {
        return $this->genericFormAction(self::$TRAINING_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/admin/ensenanzas/eliminar/{id}", name="admin_training_delete", methods={"GET", "POST"}, requirements={"id": "\d+"} )
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function trainingDeleteAction(Training $element, Request $request)
    {
        return $this->genericDeleteAction(self::$TRAINING_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/admin/diasnolectivos", name="admin_non_school_day", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function nonSchoolDayIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$NON_SCHOOL_DAY_ENTITY_DATA, $request);
    }

    /**
     * @Route("/admin/diasnolectivos/nuevo", name="admin_non_school_day_new", methods={"GET", "POST"})
     * @Route("/admin/diasnolectivos/{id}", name="admin_non_school_day_form", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function nonSchoolDayFormAction(NonSchoolDay $element = null, Request $request)
    {
        return $this->genericFormAction(self::$NON_SCHOOL_DAY_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/admin/diasnolectivos/eliminar/{id}", name="admin_non_school_day_delete", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function workcenterDeleteAction(Workcenter $element, Request $request)
    {
        return $this->genericDeleteAction(self::$NON_SCHOOL_DAY_ENTITY_DATA, $element, $request);
    }
}
