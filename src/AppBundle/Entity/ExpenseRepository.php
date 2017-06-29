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

    public function areExpensesReviewed(User $user)
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.teacher = :user')
            ->andWhere('e.reviewed = 0')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() == 0;
    }

    public function setReviewed(User $user)
    {
        return $this->createQueryBuilder('e')
            ->update()
            ->set('e.reviewed', 1)
            ->where('e.teacher = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function setPaid(User $user)
    {
        return $this->createQueryBuilder('e')
            ->update()
            ->set('e.paid', 1)
            ->where('e.teacher = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
