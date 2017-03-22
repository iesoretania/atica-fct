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

use AppBundle\Entity\Criterion;
use AppBundle\Entity\LearningOutcome;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/programa")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class CriterionController extends Controller
{
    /**
     * @Route("/criterios/{id}", name="admin_program_criteria", methods={"GET"})
     */
    public function criteriaIndexAction(LearningOutcome $learningOutcome, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $usersQuery = $em->createQuery('SELECT c FROM AppBundle:Criterion c WHERE c.learningOutcome = :learningOutcome')
            ->setParameter('learningOutcome', $learningOutcome);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'c.code',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('activity/manage_criteria.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
                'breadcrumb' => [
                    ['fixed' => $learningOutcome->getTraining()->getName(), 'path' => 'admin_program_training_learning_outcomes', 'options' => ['id' => $learningOutcome->getTraining()->getId()]],
                    ['fixed' => (string) $learningOutcome, 'path' => 'admin_program_activities', 'options' => ['id' => $learningOutcome->getId()]]
                ],
                'title' => $learningOutcome->getName(),
                'pagination' => $pagination,
                'learning_outcome' => $learningOutcome
            ]);
    }

    /**
     * @Route("/criterio/nuevo/{id}", name="admin_program_criterion_new", methods={"GET", "POST"}, requirements={"activity": "\d+"})
     */
    public function formNewCriterionAction(LearningOutcome $learningOutcome, Request $request)
    {
        $criterion = new Criterion();
        $criterion->setLearningOutcome($learningOutcome);
        $this->getDoctrine()->getManager()->persist($criterion);

        return $this->formCriterionAction($criterion, $request);
    }

    /**
     * @Route("/criterio/{id}", name="admin_program_criterion_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function formCriterionAction(Criterion $criterion, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('AppBundle\Form\Type\CriterionType', $criterion, [
            'fixed' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Guardar el criterio de evaluación en la base de datos
            // Probar a guardar los cambios
            try {
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'activity'));
                return $this->redirectToRoute('admin_program_criteria', ['id' => $criterion->getLearningOutcome()->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'activity'));
            }
        }

        $title = $criterion->getId() ? (string) $criterion : $this->get('translator')->trans('form.criterion.new', [], 'activity');

        $training = $criterion->getLearningOutcome()->getTraining();
        return $this->render('activity/form_criterion.html.twig', [
            'form' => $form->createView(),
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
            'breadcrumb' => [
                ['fixed' => $training->getName(), 'path' => 'admin_program_training_learning_outcomes', 'options' => ['id' => $training->getId()]],
                ['fixed' => (string) $criterion->getLearningOutcome(), 'path' => 'admin_program_activities', 'options' => ['id' => $criterion->getLearningOutcome()->getId()]],
                ['fixed' => $title]
            ],
            'new' => ($criterion->getId() == 0),
            'title' => $title,
            'item' => $criterion
        ]);
    }


    /**
     * @Route("/criterio/eliminar/{id}", name="admin_program_criterion_delete", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function learningOutcomeDeleteAction(Criterion $criterion, Request $request)
    {
        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            // Eliminar el criterio de la base de datos
            $this->getDoctrine()->getManager()->remove($criterion);
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.criterion.deleted', [], 'activity'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.criterion.not_deleted', [], 'activity'));
            }
            return $this->redirectToRoute('admin_program_criteria', ['id' => $criterion->getLearningOutcome()->getId()]);
        }

        $title = (string) $criterion->getName();

        $training = $criterion->getLearningOutcome()->getTraining();
        $breadcrumb = [
            ['fixed' => $training->getName(), 'path' => 'admin_program_training_learning_outcomes', 'options' => ['id' => $training->getId()]],
            ['fixed' => (string) $criterion->getLearningOutcome(), 'path' => 'admin_program_activities', 'options' => ['id' => $criterion->getLearningOutcome()->getId()]],
            ['fixed' => (string) $criterion, 'path' => 'admin_program_criterion_form', 'options' => ['id' => $criterion->getId()]],
            ['caption' => 'menu.delete']
        ];

        return $this->render('activity/delete_criterion.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'element' => $criterion
        ]);
    }
}
