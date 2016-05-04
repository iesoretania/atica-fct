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

class AdminMenu implements MenuBuilderInterface
{
    private $authorizationChecker;

    public function __construct(AuthorizationChecker $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function updateMenu(&$menu)
    {
        $isAdministrator = $this->authorizationChecker->isGranted("ROLE_ADMIN");

        if (!$isAdministrator) {
            return null;
        }

        /**
         * @var $root MenuItem
         */
        $root = reset($menu);

        $mainItem = new MenuItem();
        $mainItem
            ->setName('admin')
            ->setRouteName('admin_menu')
            ->setCaption('menu.admin')
            ->setDescription('menu.admin.detail')
            ->setColor('teal')
            ->setIcon('wrench');

        $item = new MenuItem();
        $item
            ->setName('admin.users')
            ->setRouteName('admin_users')
            ->setCaption('menu.admin.manage.users')
            ->setDescription('menu.admin.manage.users.detail')
            ->setColor('amber')
            ->setIcon('user');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.students')
            ->setRouteName('frontpage')
            ->setCaption('menu.admin.students')
            ->setDescription('menu.admin.students.detail')
            ->setColor('orange')
            ->setIcon('child');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.teachers')
            ->setRouteName('frontpage')
            ->setCaption('menu.admin.teachers')
            ->setDescription('menu.admin.teachers.detail')
            ->setColor('magenta')
            ->setIcon('graduation-cap');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.groups')
            ->setRouteName('admin_group')
            ->setCaption('menu.admin.groups')
            ->setDescription('menu.admin.groups.detail')
            ->setColor('green')
            ->setIcon('users');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.departments')
            ->setRouteName('admin_department')
            ->setCaption('menu.admin.departments')
            ->setDescription('menu.admin.departments.detail')
            ->setColor('emerald')
            ->setIcon('sitemap');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.trainings')
            ->setRouteName('admin_training')
            ->setCaption('menu.admin.trainings')
            ->setDescription('menu.admin.trainings.detail')
            ->setColor('olive')
            ->setIcon('bank');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.calendar')
            ->setRouteName('frontpage')
            ->setCaption('menu.admin.calendar')
            ->setDescription('menu.admin.calendar.detail')
            ->setColor('cyan')
            ->setIcon('calendar');

        $mainItem->addChild($item);

        $root->addChild($mainItem, MenuItem::AT_THE_BEGINNING);
    }

    public function getMenuPriority()
    {
        return '1000-Admin';
    }
}
