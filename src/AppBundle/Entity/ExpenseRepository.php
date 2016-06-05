<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ExpenseRepository extends EntityRepository
{
    public function getRelatedExpenses(User $user)
    {
        return $this->createQueryBuilder('e')
            ->where('e.teacher = :user')
            ->setParameter('user', $user)
            ->orderBy('e.date')
            ->getQuery()
            ->getResult();
    }
}
