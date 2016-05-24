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

use AppBundle\Entity\Activity;
use AppBundle\Entity\Training;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/programa")
 * @Security("is_granted('ROLE_DEPARTMENT_HEAD')")
 */
class ActivityController extends Controller
{
    /**
     * @Route("", name="admin_program", methods={"GET"})
     */
    public function teachingIndexAction()
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $teachingQuery = $em->createQuery('SELECT t FROM AppBundle:Training t ORDER BY t.name');

        $items = $teachingQuery->getResult();

        return $this->render('activity/training_index.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
                'title' => null,
                'elements' => $items
            ]);
    }

    /**
     * @Route("/{id}", name="admin_program_training", methods={"GET"})
     */
    public function groupIndexAction(Training $training, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $usersQuery = $em->createQuery('SELECT a FROM AppBundle:Activity a WHERE a.training = :training')
            ->setParameter('training', $training);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'a.code',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('activity/manage_activities.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
                'breadcrumb' => [
                    ['fixed' => $training->getName(), 'path' => 'admin_program_training', 'options' => ['id' => $training->getId()]],
                ],
                'title' => $training->getName(),
                'pagination' => $pagination,
                'training' => $training
            ]);
    }


    /**
     * @Route("/{training}/nuevo", name="admin_program_activity_new", methods={"GET", "POST"}, requirements={"training": "\d+"})
     */
    public function formNewAction(Training $training, Request $request)
    {
        $activity = new Activity();
        $activity->setTraining($training);
        $this->getDoctrine()->getManager()->persist($activity);

        return $this->formAction($activity, $request);
    }

    /**
     * @Route("/actividad/{id}", name="admin_program_activity_form", methods={"GET", "POST"}, requirements={"activity": "\d+"})
     */
    public function formAction(Activity $activity, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('AppBundle\Form\Type\ActivityType', $activity, [
            'fixed' => true
        ]);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Guardar el usuario en la base de datos
            // Probar a guardar los cambios
            try {
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'activity'));
                return $this->redirectToRoute('admin_program_training', ['id' => $activity->getTraining()->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'activity'));
            }
        }

        $titulo = $activity->getId() ? $activity->getName() : $this->get('translator')->trans('form.new', [], 'activity');

        return $this->render('activity/form_activity.twig', [
            'form' => $form->createView(),
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
            'breadcrumb' => [
                ['fixed' => $activity->getTraining()->getName(), 'path' => 'admin_program_training', 'options' => ['id' => $activity->getTraining()->getId()]],
                ['fixed' => $titulo]
            ],
            'new' => ($activity->getId() == 0),
            'title' => $titulo,
            'item' => $activity
        ]);
    }

    /**
     * @Route("/actividad/eliminar/{id}", name="admin_program_activity_delete", methods={"GET", "POST"}, requirements={"activity": "\d+"})
     */
    public function activityDeleteAction(Activity $activity, Request $request)
    {
        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            // Eliminar el departamento de la base de datos
            $this->getDoctrine()->getManager()->remove($activity);
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.deleted', [], 'activity'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], 'activity'));
            }
            return $this->redirectToRoute('admin_program_training', ['id' => $activity->getTraining()->getId()]);
        }

        $title = (string) $activity->getName();

        $breadcrumb = [
            [
                'fixed' => $title,
                'path' => 'admin_program_activity_form',
                'options' => ['activity' => $activity->getId()]
            ],
            ['caption' => 'menu.delete']
        ];

        return $this->render('activity/delete_activity.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_departments'),
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'element' => $activity
        ]);
    }
}
