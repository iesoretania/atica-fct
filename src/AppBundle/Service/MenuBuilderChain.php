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

namespace AppBundle\Service;

use AppBundle\Menu\MenuItem;

class MenuBuilderChain
{
    /**
     * @var MenuBuilderInterface[]
     */
    private $menuBuilders;

    /**
     * @var MenuItem[]|null
     */
    private $menuCache;

    public function __construct()
    {
        $this->menuBuilders = [];
        $this->menuCache = null;
    }

    public function addMenuBuilder(MenuBuilderInterface $menuBuilder)
    {
        $this->menuBuilders[$menuBuilder->getMenuPriority()] = $menuBuilder;
    }

    public function getChain()
    {
        return $this->menuBuilders;
    }

    public function getMenu()
    {
        if ($this->menuCache) {
            return $this->menuCache;
        }

        $menu = [];

        $mainItem = new MenuItem();
        $mainItem
            ->setName('frontpage')
            ->setRouteName('frontpage')
            ->setCaption('menu.frontpage')
            ->setDescription('menu.frontpage.detail')
            ->setColor('white')
            ->setIcon('home');

        $menu[] = $mainItem;

        ksort($this->menuBuilders);

        foreach ($this->menuBuilders as $menuBuilder) {
            $menuBuilder->updateMenu($menu);
        }

        $this->menuCache = $menu;

        return $menu;
    }

    public function getSubmenuItemByRouteName(MenuItem $submenu, $name)
    {
        if ($submenu->getRouteName() === $name) {
            return $submenu;
        }

        $children = $submenu->getChildren();

        foreach ($children as $item) {
            $result = $this->getSubmenuItemByRouteName($item, $name);
            if (null !== $result) {
                return $result;
            }
        }

        return null;
    }

    public function getMenuItemByRouteName($name)
    {
        $menu = $this->getMenu();
        $root = reset($menu);

        if (false === $root) {
            return null;
        }

        return $this->getSubmenuItemByRouteName($root, $name);
    }

    public function clearCache()
    {
        $this->menuCache = null;
    }
}
