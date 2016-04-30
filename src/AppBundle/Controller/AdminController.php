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

use AppBundle\Menu\MenuItem;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name="admin_menu", methods={"GET"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $menu = $this->get('app.menu_builders_chain')->getMenu();

        $menuItem = [];

        /**
         * @var MenuItem $item
         */
        foreach($menu as $item) {
            if ($item->getName() === 'admin') {
                $menuItem = $item;
            }
        }

        return $this->render('admin/menu.html.twig',
            [
                'breadcrumb' => [
                    ['caption' => $menuItem->getCaption(), 'icon' => $menuItem->getIcon()]
                ],
                'menu_item' => $menuItem
            ]);
    }
}
