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

use AppBundle\Entity\Company;
use AppBundle\Entity\Department;
use AppBundle\Entity\Group;
use AppBundle\Entity\NonSchoolDay;
use AppBundle\Entity\Person;
use AppBundle\Entity\Student;
use AppBundle\Entity\Teacher;
use AppBundle\Entity\Training;
use AppBundle\Entity\Workcenter;
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
            'query' => 'SELECT d FROM AppBundle:Department d LEFT JOIN d.head h LEFT JOIN h.person p',
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

    public static $WORKCENTER_ENTITY_DATA = [
        'entity' => 'workcenter',
        'entityClassName' => 'AppBundle\Entity\Workcenter',
        'entityFormType' => 'AppBundle\Form\Type\WorkcenterType',
        'query' => 'SELECT w FROM AppBundle:Workcenter w JOIN w.company c',
        'defaultSortFieldName' => 'w.company.name',
        'columns' => [
            ['size' => '4', 'sort_field' => 'w.company.name', 'name' => 'form.company'],
            ['size' => '5', 'sort_field' => 'w.name', 'name' => 'form.name'],
        ],
        'data_columns' => ['company', 'name']
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
            ['size' => '3', 'sort_field' => 't.department', 'name' => 'form.department'],
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

    public static $TEACHER_ENTITY_DATA = [
        'entity' => 'teacher',
        'entityClassName' => 'AppBundle\Entity\Teacher',
        'entityFormType' => 'AppBundle\Form\Type\TeacherType',
        'query' => 'SELECT t FROM AppBundle:Teacher t JOIN t.person p',
        'defaultSortFieldName' => 'p.lastName',
        'columns' => [
            ['size' => '5', 'sort_field' => 'p.lastName', 'name' => 'form.last_name'],
            ['size' => '4', 'sort_field' => 'p.firstName', 'name' => 'form.first_name']
        ],
        'data_columns' => ['lastName', 'firstName']
    ];

    public static $STUDENT_ENTITY_DATA = [
        'entity' => 'student',
        'entityClassName' => 'AppBundle\Entity\Student',
        'entityFormType' => 'AppBundle\Form\Type\StudentType',
        'query' => 'SELECT s FROM AppBundle:Student s JOIN s.person p',
        'defaultSortFieldName' => 'p.lastName',
        'columns' => [
            ['size' => '4', 'sort_field' => 'p.lastName', 'name' => 'form.last_name'],
            ['size' => '3', 'sort_field' => 'p.firstName', 'name' => 'form.first_name'],
            ['size' => '2', 'sort_field' => 's.group', 'name' => 'form.group']
        ],
        'data_columns' => ['lastName', 'firstName', 'group']
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
            if (method_exists($element, 'setPerson')) {
                $person = new Person();
                $element->setPerson($person);
                $em->persist($person);
            }
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
    public function companyFormAction(Company $element = null, Request $request)
    {
        return $this->genericFormAction(self::$COMPANY_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/empresas/eliminar/{id}", name="admin_company_delete", methods={"GET", "POST"}, requirements={"id": "\d+"} )
     */
    public function companyDeleteAction(Company $element, Request $request)
    {
        return $this->genericDeleteAction(self::$COMPANY_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/grupos", name="admin_group", methods={"GET"})
     */
    public function groupIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$GROUP_ENTITY_DATA, $request);
    }

    /**
     * @Route("/grupos/nuevo", name="admin_group_new", methods={"GET", "POST"})
     * @Route("/grupos/{id}", name="admin_group_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function groupFormAction(Group $element = null, Request $request)
    {
        return $this->genericFormAction(self::$GROUP_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/grupos/eliminar/{id}", name="admin_group_delete", methods={"GET", "POST"}, requirements={"id": "\d+"} )
     */
    public function groupDeleteAction(Group $element, Request $request)
    {
        return $this->genericDeleteAction(self::$GROUP_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/ensenanzas", name="admin_training", methods={"GET"})
     */
    public function trainingIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$TRAINING_ENTITY_DATA, $request);
    }

    /**
     * @Route("/ensenanzas/nueva", name="admin_training_new", methods={"GET", "POST"})
     * @Route("/ensenanzas/{id}", name="admin_training_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function trainingFormAction(Training $element = null, Request $request)
    {
        return $this->genericFormAction(self::$TRAINING_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/ensenanzas/eliminar/{id}", name="admin_training_delete", methods={"GET", "POST"}, requirements={"id": "\d+"} )
     */
    public function trainingDeleteAction(Training $element, Request $request)
    {
        return $this->genericDeleteAction(self::$TRAINING_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/diasnolectivos", name="admin_non_school_day", methods={"GET"})
     */
    public function nonSchoolDayIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$NON_SCHOOL_DAY_ENTITY_DATA, $request);
    }

    /**
     * @Route("/diasnolectivos/nuevo", name="admin_non_school_day_new", methods={"GET", "POST"})
     * @Route("/diasnolectivos/{id}", name="admin_non_school_day_form", methods={"GET", "POST"})
     */
    public function nonSchoolDayFormAction(NonSchoolDay $element = null, Request $request)
    {
        return $this->genericFormAction(self::$NON_SCHOOL_DAY_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/diasnolectivos/eliminar/{id}", name="admin_non_school_day_delete", methods={"GET", "POST"})
     */
    public function workcenterDeleteAction(Workcenter $element, Request $request)
    {
        return $this->genericDeleteAction(self::$WORKCENTER_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/centros", name="admin_workcenter", methods={"GET"})
     */
    public function workcenterIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$WORKCENTER_ENTITY_DATA, $request);
    }

    /**
     * @Route("/centros/nuevo", name="admin_workcenter_new", methods={"GET", "POST"})
     * @Route("/centros/{id}", name="admin_workcenter_form", methods={"GET", "POST"})
     */
    public function workcenterFormAction(Workcenter $element = null, Request $request)
    {
        return $this->genericFormAction(self::$WORKCENTER_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/centros/eliminar/{id}", name="admin_workcenter_delete", methods={"GET", "POST"})
     */
    public function nonSchoolDayDeleteAction(Workcenter $element, Request $request)
    {
        return $this->genericDeleteAction(self::$WORKCENTER_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/profesorado", name="admin_teacher", methods={"GET"})
     */
    public function teacherIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$TEACHER_ENTITY_DATA, $request);
    }

    /**
     * @Route("/profesorado/nuevo", name="admin_teacher_new", methods={"GET", "POST"})
     * @Route("/profesorado/{id}", name="admin_teacher_form", methods={"GET", "POST"})
     */
    public function teacherFormAction(Teacher $element = null, Request $request)
    {
        return $this->genericFormAction(self::$TEACHER_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/profesorado/eliminar/{id}", name="admin_teacher_delete", methods={"GET", "POST"})
     */
    public function teacherDeleteAction(Teacher $element, Request $request)
    {
        return $this->genericDeleteAction(self::$TEACHER_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/alumnado", name="admin_student", methods={"GET"})
     */
    public function studentIndexAction(Request $request)
    {
        return $this->genericIndexAction(self::$STUDENT_ENTITY_DATA, $request);
    }

    /**
     * @Route("/alumnado/nuevo", name="admin_student_new", methods={"GET", "POST"})
     * @Route("/alumnado/{id}", name="admin_student_form", methods={"GET", "POST"})
     */
    public function studentFormAction(Student $element = null, Request $request)
    {
        return $this->genericFormAction(self::$STUDENT_ENTITY_DATA, $element, $request);
    }

    /**
     * @Route("/alumnado/eliminar/{id}", name="admin_student_delete", methods={"GET", "POST"})
     */
    public function studentDeleteAction(Student $element, Request $request)
    {
        return $this->genericDeleteAction(self::$STUDENT_ENTITY_DATA, $element, $request);
    }
}
