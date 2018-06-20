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

class Attendance
{
    /**
     * @Assert\NotBlank()
     * @var string
     */
    protected $startTime1;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    protected $endTime1;

    /**
     * @var string
     */
    protected $startTime2;

    /**
     * @var string
     */
    protected $endTime2;

    /**
     * @var string
     */
    protected $startTime3;

    /**
     * @var string
     */
    protected $endTime3;

    /**
     * @var \DateTime
     */
    protected $signDate;


    public function __construct()
    {
        $this->signDate = new \DateTime();
    }

    /**
     * @return string
     */
    public function getStartTime1()
    {
        return $this->startTime1;
    }

    /**
     * @param string $startTime1
     * @return Attendance
     */
    public function setStartTime1($startTime1)
    {
        $this->startTime1 = $startTime1;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndTime1()
    {
        return $this->endTime1;
    }

    /**
     * @param string $endTime1
     * @return Attendance
     */
    public function setEndTime1($endTime1)
    {
        $this->endTime1 = $endTime1;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartTime2()
    {
        return $this->startTime2;
    }

    /**
     * @param string $startTime2
     * @return Attendance
     */
    public function setStartTime2($startTime2)
    {
        $this->startTime2 = $startTime2;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndTime2()
    {
        return $this->endTime2;
    }

    /**
     * @param string $endTime2
     * @return Attendance
     */
    public function setEndTime2($endTime2)
    {
        $this->endTime2 = $endTime2;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartTime3()
    {
        return $this->startTime3;
    }

    /**
     * @param string $startTime3
     * @return Attendance
     */
    public function setStartTime3($startTime3)
    {
        $this->startTime3 = $startTime3;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndTime3()
    {
        return $this->endTime3;
    }

    /**
     * @param string $endTime3
     * @return Attendance
     */
    public function setEndTime3($endTime3)
    {
        $this->endTime3 = $endTime3;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSignDate()
    {
        return $this->signDate;
    }

    /**
     * @param \DateTime $signDate
     * @return Attendance
     */
    public function setSignDate($signDate)
    {
        $this->signDate = $signDate;
        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->getEndTime1() <= $this->getStartTime1()) {
            $context->buildViolation('attendance.end_before_start')
                ->atPath('endTime1')
                ->addViolation();
        }
        if ($this->getStartTime2() && $this->getEndTime2() <= $this->getStartTime2()) {
            $context->buildViolation('attendance.end_before_start')
                ->atPath('endTime2')
                ->addViolation();
        }
        if ($this->getStartTime2() && $this->getStartTime2() <= $this->getEndTime1()) {
            $context->buildViolation('attendance.late_before_initial')
                ->atPath('startTime2')
                ->addViolation();
        }
        if ($this->getStartTime3() && $this->getEndTime3() <= $this->getStartTime3()) {
            $context->buildViolation('attendance.end_before_start')
                ->atPath('endTime3')
                ->addViolation();
        }

    }

}
