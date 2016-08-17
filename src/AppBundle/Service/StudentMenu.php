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

use AppBundle\Entity\User;
use AppBundle\Menu\MenuItem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class StudentMenu implements MenuBuilderInterface
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function updateMenu(&$menu)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user->getStudentGroup() || $user->getStudentAgreements()->count() === 0) {
            return null;
        }

        /**
         * @var $root MenuItem
         */
        $root = reset($menu);

        $mainItem = new MenuItem();
        $mainItem
            ->setName('student_calendar')
            ->setRouteName('student_calendar')
            ->setCaption('menu.student_calendar')
            ->setDescription('menu.student_calendar.detail')
            ->setColor('green')
            ->setIcon('calendar');

        $root->addChild($mainItem, MenuItem::AT_THE_END);
    }

    public function getMenuPriority()
    {
        return '0010-Student';
    }
}
