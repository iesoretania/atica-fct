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

    public function addMenuBuilder($menuBuilder)
    {
        $this->menuBuilders[] = $menuBuilder;
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

        foreach ($this->menuBuilders as $menuBuilder) {
            $menuStructure = $menuBuilder->getMenuStructure();
            if ($menuStructure) {
                $menu[$menuBuilder->getMenuPriority()] = $menuStructure;
            }
        }
        ksort($menu, SORT_NUMERIC);

        $this->menuCache = $menu;

        return $menu;
    }

    public function clearCache()
    {
        $this->menuCache = null;
    }
}
