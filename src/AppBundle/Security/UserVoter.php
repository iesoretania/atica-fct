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

use AppBundle\Entity\Agreement;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    const TRACK = 'USER_TRACK';
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

        if (!in_array($attribute, [self::MANAGE, self::TRACK], true)) {
            return false;
        }

        return true;
    }

    protected function voteOnTrack(User $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // si no es estudiante, denegar
        if ($subject->getStudentGroup() === null) {
            return false;
        }

        // los administradores globales siempre tienen permiso
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        // los jefes de departamento de los grupos a los que pertenece un estudiante
        // tienen permisos de seguimiento
        if ($subject->getStudentGroup()->getTraining()->getDepartment()->getHead() === $user) {
            return true;
        }

        // los tutores del grupo al que pertenece el estudiante tienen permisos de seguimiento
        if ($subject->getStudentGroup()->getTutors()->contains($user)) {
            return true;
        }

        // los tutores docentes de los acuerdos que tiene el estudiante tienen permisos de seguimiento
        if ($subject->getStudentAgreements()) {
            /** @var Agreement $agreement */
            foreach ($subject->getStudentAgreements() as $agreement) {
                if ($agreement->getEducationalTutor() === $user || $agreement->getWorkTutor() === $user) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function voteOnManage(User $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // los administradores globales siempre tienen permiso
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        // si no es estudiante, denegar
        if ($subject->getStudentGroup() === null) {
            return false;
        }

        // los jefes de departamento de los grupos a los que pertenece un estudiante
        // tienen permisos de seguimiento
        if ($subject->getStudentGroup()->getTraining()->getDepartment()->getHead() === $user) {
            return true;
        }

        // los tutores del grupo al que pertenece el estudiante tienen permisos de seguimiento
        if ($subject->getStudentGroup()->getTutors()->contains($user)) {
            return true;
        }

        return false;
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

        switch ($attribute) {
            case self::TRACK:
                return $this->voteOnTrack($subject, $token);
            case self::MANAGE:
                return $this->voteOnManage($subject, $token);
            default:
                // denegamos en cualquier otro caso
                return false;
        }
    }
}
