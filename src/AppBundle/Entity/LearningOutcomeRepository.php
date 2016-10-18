<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class LearningOutcomeRepository extends EntityRepository
{
    public function getFromAgreement(Agreement $agreement)
    {
        $em = $this->getEntityManager();
        $activities = $agreement->getActivities();

        $outcomes = new ArrayCollection();

        /** @var Activity $activity */
        foreach($activities as $activity) {
            if (false === $outcomes->contains($activity->getLearningOutcome())) {
                $outcomes->add($activity->getLearningOutcome());
            }
        }
        return $em->createQuery('SELECT l FROM AppBundle:LearningOutcome l WHERE l.id IN (:outcomes) ORDER BY l.code')
            ->setParameter('outcomes', $outcomes)
            ->getResult();
    }

    public function getLearningProgramFromAgreement(Agreement $agreement)
    {
        $learningOutcomes = $this->getFromAgreement($agreement);
        $result = [];

        foreach($learningOutcomes as $learningOutcome) {
            $item = [];
            $item['learning_outcome'] = $learningOutcome;
            $item['activities'] = $this->getEntityManager()->getRepository('AppBundle:Activity')->getFromAgreementAndLearningOutcome($agreement, $learningOutcome);
            $result[] = $item;
        }

        return $result;
    }
}
