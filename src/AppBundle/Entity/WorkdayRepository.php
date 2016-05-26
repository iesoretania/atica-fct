<?php

namespace AppBundle\Entity;

use AppBundle\Form\Model\Calendar;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
                    case 0:
                        $current->setHours(min($hours, $calendar->getHoursSun()));
                        break;
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
                    case 6:
                        $current->setHours(min($hours, $calendar->getHoursSat()));
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

    public function getArrayCalendar(Collection $workdays)
    {
        if ($workdays->isEmpty()) {
            return [];
        }

        /** @var Workday $current */
        $currentWorkday = $workdays->first();

        /** @var \DateTime $currentDate */
        $currentDate = $currentWorkday->getDate();

        $currentMonth = $currentDate->format('m');
        $currentYear = $currentDate->format('Y');
        $currentDate = new \DateTime();
        $currentDate->setDate($currentYear, $currentMonth, 1);
        $currentDate->setTime(0, 0);
        $dayOfWeek = $currentDate->format('w');
        $currentWeek = $currentDate->format('W');
        $numFillDays = (7 + (int) $dayOfWeek - 1) % 7;

        $month = [];
        $week = [];
        while ($numFillDays--) {
            $week[] = ['day' => ''];
        }

        /** @var \ArrayIterator $iterator */
        $iterator = $workdays->getIterator();

        /** @var \DateTime $targetDate */
        while ($iterator->valid() && ($targetDate = $iterator->current()->getDate()) >= $currentDate) {
            while ($targetDate >= $currentDate) {
                if ($currentWeek != $currentDate->format('W') || $currentMonth != $currentDate->format('m')) {
                    $month[] = $week;
                    $week = [];
                    $currentWeek = $currentDate->format('W');
                    if ($currentMonth != $currentDate->format('m')) {
                        $calendar[((int) $currentYear * 12 + (int) $currentMonth - 1)] = $month;
                        $dayOfWeek = $currentDate->format('w');
                        $numFillDays = (7 + (int) $dayOfWeek - 1) % 7;
                        $month = [];
                        while ($numFillDays--) {
                            $week[] = ['day' => ''];
                        }
                        $currentMonth = $currentDate->format('m');
                        $currentYear = $currentDate->format('Y');
                    }
                }

                if ($targetDate != $currentDate) {
                    $week[] = ['day' => $currentDate->format('d')];
                }
                $currentDate->add(new \DateInterval('P1D'));
                $currentDate->setTime(0, 0);
            }
            $week[] = ['day' =>  $targetDate->format('d'), 'data' => $iterator->current()];
            $iterator->next();
        }
        $month[] = $week;

        $calendar[((int) $currentYear*12 + (int) $currentMonth - 1)] = $month;

        return $calendar;
    }
}
