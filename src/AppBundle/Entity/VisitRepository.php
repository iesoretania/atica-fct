<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class VisitRepository extends EntityRepository
{
    public function getRelatedVisits(User $user)
    {
        return $this->createQueryBuilder('v')
            ->where('v.tutor = :user')
            ->setParameter('user', $user)
            ->orderBy('v.date')
            ->getQuery()
            ->getResult();
    }
}
