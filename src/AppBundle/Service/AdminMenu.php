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
            ->setRouteName('admin_student')
            ->setCaption('menu.admin.students')
            ->setDescription('menu.admin.students.detail')
            ->setColor('orange')
            ->setIcon('child');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.teachers')
            ->setRouteName('admin_teacher')
            ->setCaption('menu.admin.teachers')
            ->setDescription('menu.admin.teachers.detail')
            ->setColor('magenta')
            ->setIcon('graduation-cap');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.departments')
            ->setRouteName('admin_department')
            ->setCaption('menu.admin.departments')
            ->setDescription('menu.admin.departments.detail')
            ->setColor('olive')
            ->setIcon('sitemap');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.trainings')
            ->setRouteName('admin_training')
            ->setCaption('menu.admin.trainings')
            ->setDescription('menu.admin.trainings.detail')
            ->setColor('green')
            ->setIcon('bank');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.groups')
            ->setRouteName('admin_group')
            ->setCaption('menu.admin.groups')
            ->setDescription('menu.admin.groups.detail')
            ->setColor('emerald')
            ->setIcon('users');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.companies')
            ->setRouteName('admin_company')
            ->setCaption('menu.admin.companies')
            ->setDescription('menu.admin.companies.detail')
            ->setColor('cyan')
            ->setIcon('industry');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.workcenter')
            ->setRouteName('admin_workcenter')
            ->setCaption('menu.admin.workcenters')
            ->setDescription('menu.admin.workcenters.detail')
            ->setColor('dark-cyan')
            ->setIcon('building');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.work_tutor')
            ->setRouteName('admin_work_tutor')
            ->setCaption('menu.admin.work_tutors')
            ->setDescription('menu.admin.work_tutors.detail')
            ->setColor('dark-blue')
            ->setIcon('user-md');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.agreement')
            ->setRouteName('admin_agreement')
            ->setCaption('menu.admin.agreement')
            ->setDescription('menu.admin.agreement.detail')
            ->setColor('dark-cobalt')
            ->setIcon('link');

        $mainItem->addChild($item);

        $item = new MenuItem();
        $item
            ->setName('admin.calendar')
            ->setRouteName('admin_non_school_day')
            ->setCaption('menu.admin.calendar')
            ->setDescription('menu.admin.calendar.detail')
            ->setColor('dark-teal')
            ->setIcon('calendar');

        $mainItem->addChild($item);

        $root->addChild($mainItem, MenuItem::AT_THE_END);
    }

    public function getMenuPriority()
    {
        return '1000-Admin';
    }
}
