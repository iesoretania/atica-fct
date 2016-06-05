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

use AppBundle\Entity\Expense;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/desplazamientos")
 * @Security("is_granted('ROLE_EDUCATIONAL_TUTOR')")
 */
class ExpenseController extends Controller
{
    /**
     * @Route("", name="expense_tutor_index", methods={"GET"})
     */
    public function teacherIndexAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($this->isGranted('ROLE_ADMIN')) {
            $visits = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getEducationalTutorsExpenseSummary();
        } elseif ($this->isGranted('ROLE_DEPARTMENT_HEAD')) {
            $visits = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getEducationalTutorsByDepartmentsExpenseSummary($user->getDirects());
        } else {
            return $this->expenseIndexAction($user);
        }

        return $this->render('expense/tutor_index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('expense_tutor_index'),
            'title' => null,
            'elements' => $visits
        ]);
    }

    /**
     * @Route("/{id}", name="expense_index", methods={"GET"})
     * @Security("is_granted('USER_VISIT_TRACK', tutor)")
     */
    public function expenseIndexAction(User $tutor)
    {
        $workcenters = $this->getDoctrine()->getManager()->getRepository('AppBundle:Expense')->getRelatedExpenses($tutor);

        $title = (string) $tutor;

        return $this->render('expense/expense_index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('expense_tutor_index'),
            'breadcrumb' => [
                ['fixed' => $title]
            ],
            'title' => $this->get('translator')->trans('browse.expenses', ['%user%' => $title], 'expense'),
            'tutor' => $tutor,
            'elements' => $workcenters,
            'back_route_name' => $this->isGranted('ROLE_DEPARTMENT_HEAD') ? 'expense_tutor_index' : 'frontpage'
        ]);
    }

    /**
     * @Route("/{id}/modificar/{expense}", name="expense_form", methods={"GET", "POST"})
     * @Security("is_granted('USER_VISIT_TRACK', tutor) and expense.getTeacher() == tutor")
     */
    public function expenseFormAction(User $tutor, Expense $expense, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\ExpenseType', $expense, [
            'admin' => $this->isGranted('ROLE_FINANCIAL_MANAGER')
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->getDoctrine()->getManager()->persist($expense);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'visit'));
            return $this->redirectToRoute('expense_index', ['id' => $tutor->getId()]);
        }
        $title = $this->get('translator')->trans($expense->getId() ? 'form.view' : 'form.new', [], 'visit');

        return $this->render('expense/form_expense.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('expense_tutor_index'),
            'breadcrumb' => [
                ['fixed' => (string) $tutor, 'path' => 'visit_workcenter_index', 'options' => ['id' => $tutor->getId()]],
                ['fixed' => $title]
            ],
            'title' => $title,
            'expense' => $expense,
            'form' => $form->createView(),
            'tutor' => $tutor
        ]);
    }

    /**
     * @Route("/{id}/registrar", name="expense_form_new", methods={"GET", "POST"})
     * @Security("is_granted('USER_VISIT_TRACK', tutor)")
     */
    public function expenseNewAction(User $tutor, Request $request)
    {
        $expense = new Expense();
        $expense->setTeacher($tutor);
        $expense->setDate(new \DateTime());

        return $this->expenseFormAction($tutor, $expense, $request);
    }
}
