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

namespace AppBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username) {

        if (!$username) {
            return null;
        }
        return $this->getEntityManager()
            ->createQuery('SELECT u FROM AppBundle:User u 
                           WHERE u.loginUsername = :username
                           OR u.email = :username')
            ->setParameters([
                'username' => $username
            ])
            ->getOneOrNullResult();
    }

    public function refreshUser(UserInterface $user) {
        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class) {
        return $class === 'AppBundle\Entity\User';
    }

    public function createNewUser()
    {
        $user = new User();
        $this->getEntityManager()->persist($user);

        return $user;
    }

    public function countAgreementHours(User $user)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT SUM(w.hours) FROM AppBundle:Workday w INNER JOIN w.agreement a WHERE a.student = :user')
            ->setParameter('user', $user)
            ->getSingleScalarResult();
    }

    public function countTrackedHours(User $user)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT SUM(t.hours) FROM AppBundle:Tracking t INNER JOIN AppBundle:Workday w INNER JOIN w.agreement a WHERE a.student = :user')
            ->setParameter('user', $user)
            ->getSingleScalarResult();
    }

    public function getAgreementRelatedStudents(User $user)
    {
        $em = $this->getEntityManager();
        return $em->createQuery('SELECT DISTINCT u,
              (SELECT SUM(t2.hours) FROM AppBundle:Tracking t2 INNER JOIN AppBundle:Workday w2 WITH t2.workday = w2 INNER JOIN w2.agreement a2 WITH w2.agreement = a2 WHERE a2.student = u),
              (SELECT SUM(w3.hours) FROM AppBundle:Workday w3 INNER JOIN w3.agreement a3 WHERE a3.student = u)
              FROM AppBundle:User u JOIN AppBundle:Agreement a WITH a.student = u WHERE a.educationalTutor = :user OR a.workTutor = :user ORDER BY u.lastName, u.firstName')
            ->setParameter('user', $user)
            ->getResult();
    }

    public function getEducationalTutorsAgreementSummary()
    {
        $em = $this->getEntityManager();
        return $em->createQuery('SELECT u,
              (SELECT COUNT(a1) FROM AppBundle:Agreement a1 WHERE a1.educationalTutor = u),
              (SELECT COUNT(DISTINCT a2.student) FROM AppBundle:Agreement a2 WHERE a2.educationalTutor = u)
              FROM AppBundle:User u WHERE u IN (SELECT DISTINCT IDENTITY(a.educationalTutor) FROM AppBundle:Agreement a) ORDER BY u.lastName, u.firstName')
            ->getResult();
    }

    public function getEducationalTutorsByStudent(User $student)
    {
        $em = $this->getEntityManager();
        return $em->createQuery('SELECT u FROM AppBundle:User u WHERE u IN (SELECT DISTINCT IDENTITY(a.educationalTutor) FROM AppBundle:Agreement a INNER JOIN a.educationalTutor u2 WITH a.educationalTutor = u2 WHERE a.student = :student) ORDER BY u.lastName, u.firstName')
            ->setParameter('student', $student)
            ->getResult();
    }

    public function getEducationalTutorsByDepartmentsAgreementSummary(Collection $departments)
    {
        $em = $this->getEntityManager();
        return $em->createQuery('SELECT u,
              (SELECT COUNT(a1) FROM AppBundle:Agreement a1 WHERE a1.educationalTutor = u),
              (SELECT COUNT(DISTINCT a2.student) FROM AppBundle:Agreement a2 WHERE a2.educationalTutor = u)
              FROM AppBundle:User u WHERE u IN (SELECT DISTINCT IDENTITY(a.educationalTutor) FROM AppBundle:Agreement a JOIN AppBundle:User s WITH a.student = s JOIN AppBundle:Group g WITH s.studentGroup = g JOIN AppBundle:Training t WITH g.training = t JOIN AppBundle:Department d WITH t.department = d WHERE d IN (:departments)) ORDER BY u.lastName, u.firstName')
            ->setParameter('departments', $departments)
            ->getResult();
    }

    public function getEducationalTutorsExpenseSummary()
    {
        $em = $this->getEntityManager();
        return $em->createQuery('SELECT u,
              (SELECT SUM(e1.distance) FROM AppBundle:Expense e1 WHERE e1.teacher = u),
              (SELECT SUM(e2.distance * e2.reviewed) FROM AppBundle:Expense e2 WHERE e2.teacher = u),
              (SELECT SUM(e3.distance * e3.paid) FROM AppBundle:Expense e3 WHERE e3.teacher = u)
              FROM AppBundle:User u WHERE u IN (SELECT DISTINCT IDENTITY(a.educationalTutor) FROM AppBundle:Agreement a) ORDER BY u.lastName, u.firstName')
            ->getResult();
    }

    public function getEducationalTutorsByDepartmentsExpenseSummary(Collection $departments)
    {
        $em = $this->getEntityManager();
        return $em->createQuery('SELECT u,
              (SELECT SUM(e1.distance) FROM AppBundle:Expense e1 WHERE e1.teacher = u),
              (SELECT SUM(e2.distance * e2.reviewed) FROM AppBundle:Expense e2 WHERE e2.teacher = u),
              (SELECT SUM(e3.distance * e3.paid) FROM AppBundle:Expense e3 WHERE e3.teacher = u)
              FROM AppBundle:User u WHERE u IN (SELECT DISTINCT IDENTITY(a.educationalTutor) FROM AppBundle:Agreement a JOIN AppBundle:User s WITH a.student = s JOIN AppBundle:Group g WITH s.studentGroup = g JOIN AppBundle:Training t WITH g.training = t JOIN AppBundle:Department d WITH t.department = d WHERE d IN (:departments)) ORDER BY u.lastName, u.firstName')
            ->setParameter('departments', $departments)
            ->getResult();
    }

    public function getStudentsByWorkcenterAndEducationalTutor(Workcenter $workcenter = null, User $tutor = null)
    {
        if (null === $workcenter || null === $tutor) {
            return [];
        }

        $em = $this->getEntityManager();
        return $em->createQuery('SELECT u
              FROM AppBundle:User u WHERE u IN (SELECT DISTINCT IDENTITY(a.student) FROM AppBundle:Agreement a WHERE a.workcenter = :workcenter AND a.educationalTutor = :tutor) ORDER BY u.lastName, u.firstName')
            ->setParameter('workcenter', $workcenter)
            ->setParameter('tutor', $tutor)
            ->getResult();
    }
}
