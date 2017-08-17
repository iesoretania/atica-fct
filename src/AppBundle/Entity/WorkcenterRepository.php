<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class WorkcenterRepository extends EntityRepository
{
    public function getVisitRelatedWorkcenters(User $user, array $quarters = null)
    {
        $em = $this->getEntityManager();

        if (null === $quarters) {
            $quarters = [Agreement::FIRST_QUARTER, Agreement::SECOND_QUARTER, Agreement::THIRD_QUARTER];
        }

        $dates = $em->getRepository('AppBundle:Agreement')->getAgreementsDateRangeByEducationalTutorAndQuarters($user, $quarters);

        if ($dates[0][1] === null) {
            return [];
        }

        return $em->createQuery('SELECT DISTINCT w,
              (SELECT COUNT(a1) FROM AppBundle:Agreement a1 WHERE a1.workcenter = w AND a1.educationalTutor = :user AND a1.quarter IN (:quarters)),
              (SELECT COUNT(DISTINCT a2.student) FROM AppBundle:Agreement a2 WHERE a2.workcenter = w AND a2.educationalTutor = :user AND a2.quarter IN (:quarters)),
              (SELECT COUNT(v3) FROM AppBundle:Visit v3 WHERE v3.workcenter = w AND v3.tutor = :user AND v3.date >= :dateFrom AND v3.date <= :dateTo),
              (SELECT MAX(v4.date) FROM AppBundle:Visit v4 WHERE v4.workcenter = w AND v4.tutor = :user AND v4.date >= :dateFrom AND v4.date <= :dateTo),
              (SELECT MIN(v5.date) FROM AppBundle:Visit v5 WHERE v5.workcenter = w AND v5.tutor = :user AND v5.date >= :dateFrom AND v5.date <= :dateTo)
              FROM AppBundle:Workcenter w JOIN AppBundle:Agreement a WITH a.workcenter = w JOIN AppBundle:Company c WHERE a.educationalTutor = :user AND a.quarter IN (:quarters) ORDER BY w.name')
            ->setParameter('user', $user)
            ->setParameter('quarters', $quarters)
            ->setParameter('dateFrom', $dates[0][1])
            ->setParameter('dateTo', $dates[0][2])
            ->getResult();
    }

    public function getVisitRelatedWorkcentersByQuarters(User $user)
    {
        $quarters = [Agreement::FIRST_QUARTER, Agreement::SECOND_QUARTER, Agreement::THIRD_QUARTER];

        $result = [];

        foreach($quarters as $quarter) {
            $partial = $this->getVisitRelatedWorkcenters($user, [$quarter]);
            if ($partial) {
                $result[$quarter] = $partial;
            }
        }

        return $result;
    }

    public function getWorkcentersByEducationTutor(User $user)
    {
        $em = $this->getEntityManager();

        return $em->createQuery('SELECT DISTINCT w
              FROM AppBundle:Workcenter w JOIN AppBundle:Agreement a WITH a.workcenter = w JOIN AppBundle:Company c WHERE a.educationalTutor = :user ORDER BY w.name')
            ->setParameter('user', $user)
            ->getResult();
    }
}
