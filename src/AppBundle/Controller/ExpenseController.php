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
 * @Security("is_granted('ROLE_EDUCATIONAL_TUTOR') or is_granted('ROLE_FINANCIAL_MANAGER')")
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
        if ($this->isGranted('ROLE_FINANCIAL_MANAGER')) {
            $expenses = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getEducationalTutorsExpenseSummary();
        } elseif ($this->isGranted('ROLE_DEPARTMENT_HEAD')) {
            $expenses = $this->getDoctrine()->getManager()->getRepository('AppBundle:User')->getEducationalTutorsByDepartmentsExpenseSummary($user->getDirects());
        } else {
            return $this->expenseIndexAction($user);
        }

        return $this->render('expense/tutor_index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('expense_tutor_index'),
            'title' => null,
            'elements' => $expenses
        ]);
    }

    /**
     * @Route("/{id}", name="expense_index", methods={"GET"})
     * @Security("is_granted('ROLE_FINANCIAL_MANAGER') or is_granted('USER_VISIT_TRACK', tutor)")
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
            'back_route_name' => ($this->isGranted('ROLE_DEPARTMENT_HEAD') || $this->isGranted('ROLE_FINANCIAL_MANAGER')) ? 'expense_tutor_index' : 'frontpage'
        ]);
    }

    /**
     * @Route("/{id}/modificar/{expense}", name="expense_form", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_FINANCIAL_MANAGER') or (is_granted('USER_VISIT_TRACK', tutor) and expense.getTeacher() == tutor)")
     */
    public function expenseFormAction(User $tutor, Expense $expense, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\ExpenseType', $expense, [
            'admin' => $this->isGranted('ROLE_FINANCIAL_MANAGER'),
            'disabled' => $expense->isReviewed() && !$this->isGranted('ROLE_FINANCIAL_MANAGER')
        ]);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->getDoctrine()->getManager()->persist($expense);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'expense'));
            return $this->redirectToRoute('expense_index', ['id' => $tutor->getId()]);
        }
        $title = $this->get('translator')->trans($expense->getId() ? 'form.view' : 'form.new', [], 'expense');

        return $this->render('expense/form_expense.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('expense_tutor_index'),
            'breadcrumb' => [
                ['fixed' => (string) $tutor, 'path' => 'expense_index', 'options' => ['id' => $tutor->getId()]],
                ['fixed' => $expense->getId() ? $expense->getDate()->format('d/m/Y') : $title]
            ],
            'title' => $title,
            'expense' => $expense,
            'form' => $form->createView(),
            'tutor' => $tutor
        ]);
    }


    /**
     * @Route("/{id}/eliminar/{expense}", name="expense_delete", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_FINANCIAL_MANAGER') or (is_granted('USER_VISIT_TRACK', tutor) and expense.getTeacher() == tutor)")
     */
    public function expenseDeleteAction(User $tutor, Expense $expense, Request $request)
    {
        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            $em = $this->getDoctrine()->getManager();
            
            if (!$expense->isPaid() || $this->isGranted('ROLE_FINANCIAL_MANAGER')) {
                // Eliminar el desplazamiento de la base de datos
                $em->remove($expense);
                try {
                    $em->flush();
                    $this->addFlash('success', $this->get('translator')->trans('alert.deleted', [], 'expense'));
                } catch (\Exception $e) {
                    $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], 'expense'));
                }
            } else {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], 'expense'));
            }
            return $this->redirectToRoute('expense_index', ['id' => $tutor->getId()]);
        }

        $title = $expense->getDate()->format('d/m/Y');

        return $this->render('expense/delete_expense.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('expense_tutor_index'),
            'breadcrumb' => [
                ['fixed' => (string) $tutor, 'path' => 'expense_index', 'options' => ['id' => $tutor->getId()]],
                ['fixed' => $expense->getDate()->format('d/m/Y'), 'path' => 'expense_form', 'options' => ['id' => $tutor->getId(), 'expense' => $expense->getId()]],
                ['fixed' => $this->get('translator')->trans('form.delete', [], 'expense')]
            ],
            'title' => $title,
            'tutor' => $tutor,
            'expense' => $expense
        ]);
    }

    /**
     * @Route("/{id}/registrar", name="expense_form_new", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_FINANCIAL_MANAGER') or is_granted('USER_VISIT_TRACK', tutor)")
     */
    public function expenseNewAction(User $tutor, Request $request)
    {
        $expense = new Expense();
        $expense->setTeacher($tutor);
        $expense->setDate(new \DateTime());

        return $this->expenseFormAction($tutor, $expense, $request);
    }
}
