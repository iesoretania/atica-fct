<?php

namespace AppBundle\Entity;

use AppBundle\Form\Model\Calendar;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

class WorkdayRepository extends EntityRepository
{
    public function createCalendar(Calendar $calendar, Agreement $agreement)
    {
        $date = $calendar->getStartDate();
        $hours = $calendar->getTotalHours();
        $nonSchoolDays = $this->getEntityManager()->getRepository('AppBundle:NonSchoolDay')
            ->createQueryBuilder('n')
            ->select('n.date')
            ->getQuery()->getArrayResult();

        $nonSchoolDays = array_map('current', $nonSchoolDays);

        $collection = new ArrayCollection();

        while ($hours > 0) {
            if (false === in_array($date, $nonSchoolDays, false)) {
                $current = new Workday();
                $current->setDate(clone $date);
                $current->setAgreement($agreement);
                switch ($date->format('w')) {
                    case 1:
                        $current->setHours(min($hours, $calendar->getHoursMon()));
                        break;
                    case 2:
                        $current->setHours(min($hours, $calendar->getHoursTue()));
                        break;
                    case 3:
                        $current->setHours(min($hours, $calendar->getHoursWed()));
                        break;
                    case 4:
                        $current->setHours(min($hours, $calendar->getHoursThu()));
                        break;
                    case 5:
                        $current->setHours(min($hours, $calendar->getHoursFri()));
                        break;
                }

                $hours -= $current->getHours();

                if ($current->getHours()) {
                    $collection->add($current);
                    $this->getEntityManager()->persist($current);
                }
            }

            $date->add(new \DateInterval('P1D'));
        }
        return $collection;
    }
}
