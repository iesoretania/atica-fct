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
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class HeadDepartmentMenu implements MenuBuilderInterface
{
    private $authorizationChecker;

    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function updateMenu(&$menu)
    {
        $isHeadOfDepartment = $this->authorizationChecker->isGranted("ROLE_DEPARTMENT_HEAD");

        if (!$isHeadOfDepartment) {
            return null;
        }

        $mainItem = reset($menu);
        
        $item = new MenuItem();
        $item
            ->setName('companies')
            ->setRouteName('company_index')
            ->setCaption('menu.companies')
            ->setDescription('menu.companies.detail')
            ->setColor('dark-cyan')
            ->setIcon('building');

        $mainItem->addChild($item);
    }

    public function getMenuPriority()
    {
        return '0100-FCT-Admin';
    }
}
