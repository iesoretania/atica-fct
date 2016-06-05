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
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class StudentTrackingMenu implements MenuBuilderInterface
{
    private $tokenStorage;
    private $authorizationChecker;


    public function __construct(AuthorizationChecker $authorizationChecker, TokenStorageInterface $tokenStorage)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function updateMenu(&$menu)
    {
        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        /**
         * @var $root MenuItem
         */
        $root = reset($menu);

        if ($user->getEducationalTutorAgreements()->count() !== 0 || $user->getWorkTutorAgreements()->count() !== 0) {
            $menuItem = new MenuItem();
            $menuItem
                ->setName('my_students_tracking')
                ->setRouteName('my_student_index')
                ->setCaption('menu.student_tracking')
                ->setDescription('menu.student_tracking.detail')
                ->setColor('orange')
                ->setIcon('briefcase');

            $root->addChild($menuItem, MenuItem::AT_THE_END);
        }

        $check = ($this->authorizationChecker->isGranted('ROLE_DEPARTMENT_HEAD') || $user->getEducationalTutorAgreements()->count() !== 0);

        if ($check) {
            $menuItem = new MenuItem();
            $menuItem
                ->setName('my_students_visit')
                ->setRouteName('visit_index')
                ->setCaption('menu.student_visit')
                ->setDescription('menu.student_visit.detail')
                ->setColor('yellow')
                ->setIcon('car');

            $root->addChild($menuItem, MenuItem::AT_THE_END);
        }

        if ($this->authorizationChecker->isGranted('ROLE_FINANCIAL_MANAGER') || $check) {
            $menuItem = new MenuItem();
            $menuItem
                ->setName('travel_expenses')
                ->setRouteName('expense_tutor_index')
                ->setCaption('menu.travel_expenses')
                ->setDescription('menu.travel_expenses.detail')
                ->setColor('cyan')
                ->setIcon('road');

            $root->addChild($menuItem, MenuItem::AT_THE_END);
        }
    }

    public function getMenuPriority()
    {
        return '0075-Student-Tracking';
    }
}
