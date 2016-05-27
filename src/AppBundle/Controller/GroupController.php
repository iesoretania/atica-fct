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

use AppBundle\Entity\Group;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/grupos")
 * @Security("is_granted('ROLE_GROUP_TUTOR')")
 */
class GroupController extends Controller
{
    /**
     * @Route("", name="admin_tutor_group", methods={"GET"})
     */
    public function groupIndexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $qb = $em->getRepository('AppBundle:Group')
            ->createQueryBuilder('g')
            ->innerJoin('g.training', 't')
            ->innerJoin('t.department', 'd')
            ->orderBy('d.name')
            ->addOrderBy('t.name')
            ->addOrderBy('g.name');

        if (!$user->isGlobalAdministrator()) {
            $qb = $qb
                ->where('g.id IN (:groups)')
                ->orWhere('d.head = :user')
                ->setParameter('groups', $user->getTutorizedGroups()->toArray())
                ->setParameter('user', $user);
        }

        $items = $qb->getQuery()->getResult();

        if (count($items) == 1) {
            return $this->groupDetailIndexAction($items[0], $request);
        }

        return $this->render('group/group_index.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'title' => null,
                'elements' => $items
            ]);
    }

    /**
     * @Route("/{id}", name="admin_group_students", methods={"GET"})
     * @Security("is_granted('ROLE_GROUP_ADMIN', group)")
     */
    public function groupDetailIndexAction(Group $group, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $usersQuery = $em->createQuery('SELECT u FROM AppBundle:User u WHERE u.studentGroup = :group')
            ->setParameter('group', $group);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $usersQuery,
            $request->query->getInt('page', 1),
            $this->getParameter('page.size'),
            [
                'defaultSortFieldName' => 'u.lastName',
                'defaultSortDirection' => 'asc'
            ]
        );

        return $this->render('group/manage_students.html.twig',
            [
                'menu_item' => $this->get('app.menu_builders_chain')->getMenuItemByRouteName('admin_tutor_group'),
                'breadcrumb' => [
                    ['fixed' => $group->getName(), 'path' => 'admin_group_students', 'options' => ['id' => $group->getId()]],
                ],
                'title' => $group->getName(),
                'pagination' => $pagination,
                'user' => $this->getUser()
            ]);
    }
}
