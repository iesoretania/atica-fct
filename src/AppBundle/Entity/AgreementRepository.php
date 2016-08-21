<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AgreementRepository extends EntityRepository
{
    public function countHours(Agreement $agreement)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT SUM(w.hours) FROM AppBundle:Workday w INNER JOIN w.agreement a WHERE w.agreement = :agreement')
            ->setParameter('agreement', $agreement)
            ->getSingleScalarResult();
    }

    public function getRealFromDate(Agreement $agreement)
    {
        $date = $this->getEntityManager()
            ->createQuery('SELECT MIN(w.date) FROM AppBundle:Workday w INNER JOIN w.agreement a WHERE w.agreement = :agreement')
            ->setParameter('agreement', $agreement)
            ->getSingleScalarResult();

        return null === $date ?: new \DateTime($date);
    }

    public function getRealToDate(Agreement $agreement)
    {
        $date = $this->getEntityManager()
            ->createQuery('SELECT MAX(w.date) FROM AppBundle:Workday w INNER JOIN w.agreement a WHERE w.agreement = :agreement')
            ->setParameter('agreement', $agreement)
            ->getSingleScalarResult();

        return null === $date ?: new \DateTime($date);
    }

    public function getActivitiesStats(Agreement $agreement)
    {
        $activities = $agreement->getActivities();

        $result = $this->getEntityManager()
            ->createQuery('SELECT a, SUM(t.hours), COUNT(a) FROM AppBundle:Activity a LEFT JOIN AppBundle:Tracking t WITH t.activity = a.id LEFT JOIN t.workday w WITH w.agreement = :agreement WHERE a.id IN (:activities) GROUP BY a.code, a.name')
            ->setParameter('agreement', $agreement)
            ->setParameter('activities', $activities)
            ->getResult();

        return $result;
    }

    public function delete(Agreement $agreement)
    {
        // borrar informe si estaba cumplimentado
        if ($agreement->getReport()) {
            $this->getEntityManager()->remove($agreement->getReport());
        }

        // borrar calendario
        foreach ($agreement->getWorkdays() as $workday) {
            $this->getEntityManager()->remove($workday);
        }

        // borrar actividades de plan de formación individualizado
        $agreement->getActivities()->clear();

        // borrar acuerdo
        $this->getEntityManager()->remove($agreement);
    }

    public function getTutorizedAgreements(User $student, User $user)
    {
        $em = $this->getEntityManager();
        return $em->createQuery('SELECT a FROM AppBundle:Agreement a WHERE a.student = :student AND (a.educationalTutor = :user OR a.workTutor = :user) ORDER BY a.fromDate, a.toDate')
            ->setParameter('user', $user)
            ->setParameter('student', $student)
            ->getResult();

    }
}
