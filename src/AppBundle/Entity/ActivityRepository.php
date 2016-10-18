<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ActivityRepository extends EntityRepository
{
    public function getFromAgreementAndLearningOutcome(Agreement $agreement, LearningOutcome $learningOutcome)
    {
        $em = $this->getEntityManager();
        $activities = $agreement->getActivities();

        return $em->createQuery('SELECT a FROM AppBundle:Activity a WHERE a.id IN (:activities) AND a.learningOutcome = :outcome ORDER BY a.code')
            ->setParameter('outcome', $learningOutcome)
            ->setParameter('activities', $activities)
            ->getResult();
    }
}
