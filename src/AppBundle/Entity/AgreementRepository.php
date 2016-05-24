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
}
