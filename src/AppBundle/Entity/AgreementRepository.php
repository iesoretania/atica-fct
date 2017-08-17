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
            ->createQuery('SELECT a, SUM(CASE WHEN w.agreement = :agreement THEN t.hours ELSE 0 END), SUM(CASE WHEN w.agreement = :agreement THEN 1 ELSE 0 END) FROM AppBundle:Activity a LEFT JOIN AppBundle:Tracking t WITH t.activity = a.id LEFT JOIN AppBundle:Workday w WITH t.workday = w WHERE a.id IN (:activities) GROUP BY a.code, a.id')
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

    public function getAgreementsByWorkcenterAndEducationalTutor(Workcenter $workcenter = null, User $tutor = null)
    {
        if (null === $workcenter || null === $tutor) {
            return [];
        }

        $em = $this->getEntityManager();
        return $em->createQuery('SELECT a
              FROM AppBundle:Agreement a JOIN a.student s WHERE a.workcenter = :workcenter AND a.educationalTutor = :tutor ORDER BY a.quarter, s.lastName, s.firstName')
            ->setParameter('workcenter', $workcenter)
            ->setParameter('tutor', $tutor)
            ->getResult();
    }

    public function getAgreementsDateRangeByEducationalTutorAndQuarters(User $tutor = null, array $quarters = null)
    {
        if (null === $tutor) {
            return [];
        }

        if (null === $quarters) {
            $quarters = [Agreement::FIRST_QUARTER, Agreement::SECOND_QUARTER, Agreement::THIRD_QUARTER];
        }

        $em = $this->getEntityManager();
        return $em->createQuery('SELECT MIN(a.fromDate), MAX(a.toDate)
              FROM AppBundle:Agreement a WHERE a.educationalTutor = :tutor AND a.quarter IN (:quarters)')
            ->setParameter('tutor', $tutor)
            ->setParameter('quarters', $quarters)
            ->getResult();
    }

}
