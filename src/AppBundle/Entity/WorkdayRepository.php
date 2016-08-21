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
                $current = $this->getEntityManager()->getRepository('AppBundle:Workday')->findOneBy([
                    'date' => $date,
                    'agreement' => $agreement
                ]);
                if (null === $current) {
                    $current = new Workday();
                }
                $current->setDate(clone $date);
                $current->setAgreement($agreement);
                $assignedHours = 0;
                switch ($date->format('w')) {
                    case 0:
                        $assignedHours = min($hours, $calendar->getHoursSun());
                        break;
                    case 1:
                        $assignedHours = min($hours, $calendar->getHoursMon());
                        break;
                    case 2:
                        $assignedHours = min($hours, $calendar->getHoursTue());
                        break;
                    case 3:
                        $assignedHours = min($hours, $calendar->getHoursWed());
                        break;
                    case 4:
                        $assignedHours = min($hours, $calendar->getHoursThu());
                        break;
                    case 5:
                        $assignedHours = min($hours, $calendar->getHoursFri());
                        break;
                    case 6:
                        $assignedHours = min($hours, $calendar->getHoursSat());
                        break;
                }
                $current->setHours($current->getHours() + $assignedHours);

                $hours -= $assignedHours;

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

        $calendar = [];
        
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

        $count = 0;
        $locked = 0;

        /** @var \ArrayIterator $iterator */
        $iterator = $workdays->getIterator();

        /** @var \DateTime $targetDate */
        while ($iterator->valid() && ($targetDate = $iterator->current()->getDate()) >= $currentDate) {
            while ($targetDate >= $currentDate) {
                if ($currentWeek != $currentDate->format('W') || $currentMonth != $currentDate->format('m')) {
                    $month[(int) $currentWeek] = ['days' => $week, 'count' => $count, 'locked' => $locked];
                    $week = [];
                    $count = 0;
                    $locked = 0;
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
                    $currentWeek = $currentDate->format('W');
                }

                if ($targetDate != $currentDate) {
                    $week[] = ['day' => $currentDate->format('d')];
                }
                $currentDate->add(new \DateInterval('P1D'));
                $currentDate->setTime(0, 0);
            }
            $week[] = ['day' =>  $targetDate->format('d'), 'data' => $iterator->current()];
            $count++;
            if ($iterator->current()->isLocked()) {
                $locked++;
            }
            $iterator->next();
        }
        $month[(int) $currentWeek] = ['days' => $week, 'count' => $count, 'locked' => $locked];

        $calendar[((int) $currentYear * 12 + (int) $currentMonth - 1)] = $month;

        return $calendar;
    }

    public function getNext(Workday $workday)
    {
        $query = $this->createQueryBuilder('w')
            ->where('w.agreement = :agreement')
            ->andWhere('w.date > :date')
            ->orderBy('w.date', 'ASC')
            ->setParameter('date', $workday->getDate())
            ->setParameter('agreement', $workday->getAgreement())
            ->getQuery();

        $result = $query->setMaxResults(1)->getResult();

        return $result ? $result[0] : null;
    }

    public function getPrevious(Workday $workday)
    {
        $query = $this->createQueryBuilder('w')
            ->where('w.agreement = :agreement')
            ->andWhere('w.date < :date')
            ->orderBy('w.date', 'DESC')
            ->setParameter('date', $workday->getDate())
            ->setParameter('agreement', $workday->getAgreement())
            ->getQuery();

        $result = $query->setMaxResults(1)->getResult();

        return $result ? $result[0] : null;
    }

    public function getWorkdaysInRange(Agreement $agreement, \DateTime $start, \DateTime $end)
    {
        return $this->createQueryBuilder('w')
            ->where('w.agreement = :agreement')
            ->andWhere('w.date >= :start')
            ->andWhere('w.date <= :end')
            ->orderBy('w.date', 'DESC')
            ->setParameter('agreement', $agreement)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();
    }

    public function getWorkdaysInWeek(Agreement $agreement, $week, $year)
    {
        $date = new \DateTime();
        $date->setTime(0, 0, 0);
        $start = $date->setISODate($year, $week);
        $end = clone $date;
        $end = $end->modify('+6 days');

        return $this->getWorkdaysInRange($agreement, $start, $end);
    }

    public function isWeekLocked(Agreement $agreement, $week, $year)
    {
        $workdays = $this->getWorkdaysInWeek($agreement, $week, $year);

        $count = 0;
        $locked = 0;

        /** @var Workday $workday */
        foreach ($workdays as $workday) {
            $count++;
            if ($workday->isLocked()) {
                $locked++;
            }
        }

        // la semana está bloqueada si hay días de trabajo y todos están bloqueados
        return $count && ($count === $locked);
    }

    public function getWeekInformation(Workday $workday)
    {
        $total = 0;
        $current = 0;

        $oldNumWeek = NAN;

        $workDays = $workday->getAgreement()->getWorkdays();

        /** @var Workday $day */
        foreach($workDays as $day) {
            $numWeek = $day->getDate()->format('W');

            if ($numWeek != $oldNumWeek) {
                $total++;
                $oldNumWeek = $numWeek;
            }

            if ($workday->getDate() == $day->getDate()) {
                $current = $total;
            }
        }

        return ['total' => $total, 'current' => $current];
    }
}
