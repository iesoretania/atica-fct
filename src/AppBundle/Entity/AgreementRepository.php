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
}
