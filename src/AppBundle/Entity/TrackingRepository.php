<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TrackingRepository extends EntityRepository
{
    public function getTrackingByWorkdayAndActivity(Workday $workday, Activity $activity)
    {
        $tracking = $this->findOneBy(['workday' => $workday, 'activity' => $activity]);

        if (null === $tracking) {
            $tracking = new Tracking();
            $tracking
                ->setWorkday($workday)
                ->setActivity($activity)
                ->setHours(0.0);
        }

        return $tracking;
    }
    
    public function updateTrackingByWorkday(Workday $workday)
    {
        foreach ($workday->getAgreement()->getActivities() as $activity) {
            $this->getTrackingByWorkdayAndActivity($workday, $activity);
        }
    }
}
