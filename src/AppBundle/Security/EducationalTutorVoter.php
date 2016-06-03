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

namespace AppBundle\Security;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class EducationalTutorVoter extends Voter
{
    const VISIT_TRACK = 'USER_VISIT_TRACK';
    const MANAGE = 'USER_MANAGE';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager) {
        $this->decisionManager = $decisionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        if (!$subject instanceof User) {
            return false;
        }

        if (!in_array($attribute, [self::MANAGE, self::VISIT_TRACK], true)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject instanceof User) {
            return false;
        }

        /** @var User $user */
        $user = $token->getUser();

        // si el usuario no ha entrado, denegar
        if (!$user instanceof User) {
            return false;
        }

        $user = $token->getUser();

        // si no es tutor docente, denegar
        if ($subject->getEducationalTutorAgreements()->isEmpty()) {
            return false;
        }

        // los administradores globales siempre tienen permiso
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        // los jefes de departamento de los grupos a los que pertenece un tutor laboral
        // tienen permisos de seguimiento
        if ($subject->getEducationalTutorAgreements()->first()->getStudent()->getStudentGroup()->getTraining()->getDepartment()->getHead() === $user) {
            return $attribute != self::MANAGE;
        }

        // el propio tutor puede seguir sus visitas
        if ($subject == $user) {
            return $attribute === self::VISIT_TRACK;
        }
        
        // denegar en cualquier otro caso
        return false;
    }
}
