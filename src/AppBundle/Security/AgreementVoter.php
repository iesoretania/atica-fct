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

class AgreementVoter extends Voter
{
    const MANAGE = 'AGREEMENT_MANAGE';
    const LOCK = 'AGREEMENT_LOCK';
    const UNLOCK = 'AGREEMENT_UNLOCK';
    const ACCESS = 'AGREEMENT_ACCESS';
    const FILL = 'AGREEMENT_FILL';
    const REPORT = 'AGREEMENT_REPORT';

    private $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager) {
        $this->decisionManager = $decisionManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {

        if (!$subject instanceof Agreement) {
            return false;
        }

        if (!in_array($attribute, [self::MANAGE, self::LOCK, self::UNLOCK, self::ACCESS, self::FILL, self::REPORT], true)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!$subject instanceof Agreement) {
            return false;
        }

        // los administradores globales siempre tienen permiso
        if ($this->decisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            // si el usuario no ha entrado, denegar
            return false;
        }

        // Si es el jefe de departamento, permitir siempre
        if ($user->getDirects()->contains($subject->getStudent()->getStudentGroup()->getTraining()->getDepartment())) {
            return true;
        }

        // Si es el tutor de grupo o tutor docente, permitir cualquier cosa salvo gestión
        if ($user->getTutorizedGroups()->contains($subject->getStudent()->getStudentGroup())
                || $subject->getEducationalTutor() === $user) {
            return $attribute !== self::MANAGE;
        }

        // Si es el tutor laboral, permitir acceso al calendario y al informe
        if ($subject->getWorkTutor() === $user) {
            return  $attribute !== self::FILL && ($attribute === self::ACCESS || $attribute === self::REPORT);
        }

        // Si es propio usuario, denegar permitir sólo acceso
        if ($subject->getStudent() === $user) {
            return $attribute === self::ACCESS || $attribute === self::FILL;
        }

        // denegamos en cualquier otro caso
        return false;
    }
}
