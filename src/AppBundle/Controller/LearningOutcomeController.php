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

use AppBundle\Entity\LearningOutcome;
use AppBundle\Entity\Training;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/programa")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class LearningOutcomeController extends Controller
{
    /**
     * @Route("/{id}", name="admin_program_training_learning_outcomes", methods={"GET"})
     */
    public function groupIndexAction(Training $training, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $usersQuery = $em->createQuery('SELECT l FROM AppBundle:LearningOutcome l WHERE l.training = :training')
            ->setParameter('training', $training);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'l.code',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('activity/manage_learning_outcomes.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
                'breadcrumb' => [
                    ['fixed' => $training->getName()],
                ],
                'title' => $training->getName(),
                'pagination' => $pagination,
                'training' => $training
            ]);
    }

    /**
     * @Route("/resultado/nuevo/{training}", name="admin_program_learning_outcome_new", methods={"GET", "POST"}, requirements={"training": "\d+"})
     */
    public function formNewLearningOutcomeAction(Training $training, Request $request)
    {
        $learningOutcome = new LearningOutcome();
        $learningOutcome->setTraining($training);
        $this->getDoctrine()->getManager()->persist($learningOutcome);

        return $this->formLearningOutcomeAction($learningOutcome, $request);
    }

    /**
     * @Route("/resultado/{id}", name="admin_program_learning_outcome_form", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function formLearningOutcomeAction(LearningOutcome $learningOutcome, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('AppBundle\Form\Type\LearningOutcomeType', $learningOutcome, [
            'fixed' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Guardar el usuario en la base de datos
            // Probar a guardar los cambios
            try {
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'activity'));
                return $this->redirectToRoute('admin_program_training_learning_outcomes', ['id' => $learningOutcome->getTraining()->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_saved', [], 'activity'));
            }
        }

        $titulo = $learningOutcome->getId() ? (string) $learningOutcome : $this->get('translator')->trans('form.learning_outcome.new', [], 'activity');

        return $this->render('activity/form_learning_outcome.html.twig', [
            'form' => $form->createView(),
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
            'breadcrumb' => [
                ['fixed' => $learningOutcome->getTraining()->getName(), 'path' => 'admin_program_training_learning_outcomes', 'options' => ['id' => $learningOutcome->getTraining()->getId()]],
                ['fixed' => $titulo]
            ],
            'new' => ($learningOutcome->getId() == 0),
            'title' => $titulo,
            'item' => $learningOutcome
        ]);
    }


    /**
     * @Route("/resultado/eliminar/{id}", name="admin_program_learning_outcome_delete", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function learningOutcomeDeleteAction(LearningOutcome $learningOutcome, Request $request)
    {
        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            // Eliminar el resultado de aprendizaje de la base de datos
            $this->getDoctrine()->getManager()->remove($learningOutcome);
            try {
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.learning_outcome.deleted', [], 'activity'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.learning_outcome.not_deleted', [], 'activity'));
            }
            return $this->redirectToRoute('admin_program_training_learning_outcomes', ['id' => $learningOutcome->getTraining()->getId()]);
        }

        $title = (string) $learningOutcome->getName();

        $breadcrumb = [
            ['fixed' => $learningOutcome->getTraining()->getName(), 'path' => 'admin_program_training_learning_outcomes', 'options' => ['id' => $learningOutcome->getTraining()->getId()]],
            [
                'fixed' => $title,
                'path' => 'admin_program_learning_outcome_form',
                'options' => ['id' => $learningOutcome->getId()]
            ],
            ['caption' => 'menu.delete']
        ];

        return $this->render('activity/delete_learning_outcome.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_program'),
            'breadcrumb' => $breadcrumb,
            'title' => $title,
            'element' => $learningOutcome
        ]);
    }
}
