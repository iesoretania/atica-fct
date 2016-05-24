<?php
/*
  GESTCONV - Aplicación web para la gestión de la convivencia en centros educativos

  Copyright (C) 2015: Luis Ramón López López

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].
*/

namespace AppBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Calendar
{
    /**
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @Assert\GreaterThanOrEqual(value="1")
     * @var float
     */
    protected $totalHours;

    /**
     * @Assert\Range(min="0", max="24")
     * @var float
     */
    protected $hoursMon;

    /**
     * @Assert\Range(min="0", max="24")
     * @var float
     */
    protected $hoursTue;

    /**
     * @Assert\Range(min="0", max="24")
     * @var float
     */
    protected $hoursWed;

    /**
     * @Assert\Range(min="0", max="24")
     * @var float
     */
    protected $hoursThu;

    /**
     * @Assert\Range(min="0", max="24")
     * @var float
     */
    protected $hoursFri;

    public function __construct($total = 0)
    {
        $this->startDate = new \DateTime();

        $this->totalHours = $total;

        $this->hoursMon = 0;
        $this->hoursTue = 0;
        $this->hoursWed = 0;
        $this->hoursThu = 0;
        $this->hoursFri = 0;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     * @return Calendar
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalHours()
    {
        return $this->totalHours;
    }

    /**
     * @param int $totalHours
     * @return Calendar
     */
    public function setTotalHours($totalHours)
    {
        $this->totalHours = $totalHours;
        return $this;
    }

    /**
     * @return int
     */
    public function getHoursMon()
    {
        return $this->hoursMon;
    }

    /**
     * @param int $hoursMon
     * @return Calendar
     */
    public function setHoursMon($hoursMon)
    {
        $this->hoursMon = $hoursMon;
        return $this;
    }

    /**
     * @return int
     */
    public function getHoursTue()
    {
        return $this->hoursTue;
    }

    /**
     * @param int $hoursTue
     * @return Calendar
     */
    public function setHoursTue($hoursTue)
    {
        $this->hoursTue = $hoursTue;
        return $this;
    }

    /**
     * @return int
     */
    public function getHoursWed()
    {
        return $this->hoursWed;
    }

    /**
     * @param int $hoursWed
     * @return Calendar
     */
    public function setHoursWed($hoursWed)
    {
        $this->hoursWed = $hoursWed;
        return $this;
    }

    /**
     * @return int
     */
    public function getHoursThu()
    {
        return $this->hoursThu;
    }

    /**
     * @param int $hoursThu
     * @return Calendar
     */
    public function setHoursThu($hoursThu)
    {
        $this->hoursThu = $hoursThu;
        return $this;
    }

    /**
     * @return int
     */
    public function getHoursFri()
    {
        return $this->hoursFri;
    }

    /**
     * @param int $hoursFri
     * @return Calendar
     */
    public function setHoursFri($hoursFri)
    {
        $this->hoursFri = $hoursFri;
        return $this;
    }
    
    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->getHoursMon() + $this->getHoursTue() + $this->getHoursWed()
            + $this->getHoursThu() + $this->getHoursFri() <= 0) {
        
            $context->buildViolation('calendar.no_hours')
                ->atPath('hoursMon')
                ->addViolation();
        }
    }

}
