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
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/empresas")
 * @Security("is_granted('ROLE_DEPARTMENT_HEAD')")
 */
class CompanyController extends BaseController
{
    /**
     * @Route("", name="company_index", methods={"GET"})
     */
    public function companyIndexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT c FROM AppBundle:Company c JOIN AppBundle:User u WITH c.manager = u');

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'c.name',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('company/company_index.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('company_index'),
            'title' => null,
            'elements' => $pagination
        ]);
    }

    /**
     * @Route("/eliminar/{id}", name="company_delete", methods={"GET", "POST"})
     */
    public function companyDeleteAction(Company $company, Request $request)
    {
        if ('POST' === $request->getMethod() && $request->request->has('delete')) {

            $em = $this->getDoctrine()->getManager();

            // Eliminar el desplazamiento de la base de datos
            $em->remove($company);
            try {
                $em->flush();
                $this->addFlash('success', $this->get('translator')->trans('alert.deleted', [], 'company'));
            } catch (\Exception $e) {
                $this->addFlash('error', $this->get('translator')->trans('alert.not_deleted', [], 'company'));
            }
            return $this->redirectToRoute('company_index');
        }

        $title = (string) $company;

        return $this->render('company/delete_company.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('company_index'),
            'breadcrumb' => [
                ['fixed' => $title, 'path' => 'company_form', 'options' => ['id' => $company->getId()]],
                ['fixed' => $this->get('translator')->trans('form.delete', [], 'expense')]
            ],
            'title' => $title,
            'company' => $company
        ]);
    }

    /**
     * @Route("/{id}", name="company_form", methods={"GET", "POST"})
     */
    public function companyFormAction(Company $company, Request $request)
    {
        $form = $this->createForm('AppBundle\Form\Type\CompanyType', $company);
        $form->handleRequest($request);

        if ($form->isValid() && $form->isSubmitted()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', $this->get('translator')->trans('alert.saved', [], 'visit'));
            return $this->redirectToRoute('company_index');
        }
        $title = $company->getId() ? (string) $company : $this->get('translator')->trans('form.new', [], 'visit');

        return $this->render('company/form_company.html.twig', [
            'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('company_index'),
            'breadcrumb' => [
                ['fixed' => $title]
            ],
            'title' => $title,
            'form' => $form->createView(),
            'company' => $company
        ]);
    }

    /**
     * @Route("/nueva", name="company_new", methods={"GET", "POST"})
     */
    public function companyNewFormAction(Request $request)
    {
        $company = new Company();
        $this->getDoctrine()->getManager()->persist($company);

        return $this->companyFormAction($company, $request);
    }
}
