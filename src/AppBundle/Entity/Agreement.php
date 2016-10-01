<?php
/*
  ÁTICA - Aplicación web para la gestión documental de centros educativos

  Copyright (C) 2015-2016: Luis Ramón López López

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

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AgreementRepository")
 */
class Agreement
{
    const FIRST_QUARTER = 1;
    const SECOND_QUARTER = 2;
    const THIRD_QUARTER = 3;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @var \DateTime
     */
    protected $signDate;

    /**
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $fromDate;

    /**
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $toDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    protected $quarter;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="studentAgreements")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    protected $student;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="workTutorAgreements")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    protected $workTutor;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="educationalTutorAgreements")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    protected $educationalTutor;

    /**
     * @ORM\ManyToOne(targetEntity="Workcenter", inversedBy="agreements")
     * @ORM\OrderBy({"name"="ASC"})
     * @ORM\JoinColumn(nullable=false)
     * @var Workcenter
     */
    protected $workcenter;

    /**
     * @ORM\ManyToMany(targetEntity="Activity")
     * @ORM\OrderBy({"code"="ASC", "name"="ASC"})
     * @Assert\Count(min="1", minMessage="agreement.no_activities")
     * @var Collection
     */
    protected $activities;

    /**
     * @ORM\OneToMany(targetEntity="Workday", mappedBy="agreement")
     * @ORM\OrderBy({"date"="ASC"})
     * @var Collection
     */
    protected $workdays;

    /**
     * @ORM\OneToOne(targetEntity="Report", mappedBy="agreement")
     * @var Report
     */
    protected $report;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->workdays = new ArrayCollection();

        $this->fromDate = new \DateTime();
        $this->toDate = new \DateTime();
    }

    public function __toString()
    {
        return $this->getStudent() ? $this->getStudent() . ' - ' . $this->getWorkcenter() : '';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set signDate
     *
     * @param \DateTime $signDate
     *
     * @return Agreement
     */
    public function setSignDate($signDate)
    {
        $this->signDate = $signDate;

        return $this;
    }

    /**
     * Get signDate
     *
     * @return \DateTime
     */
    public function getSignDate()
    {
        return $this->signDate;
    }

    /**
     * Set fromDate
     *
     * @param \DateTime $fromDate
     *
     * @return Agreement
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    /**
     * Get fromDate
     *
     * @return \DateTime
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }

    /**
     * Set toDate
     *
     * @param \DateTime $toDate
     *
     * @return Agreement
     */
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;

        return $this;
    }

    /**
     * Get toDate
     *
     * @return \DateTime
     */
    public function getToDate()
    {
        return $this->toDate;
    }

    /**
     * Set student
     *
     * @param User $student
     *
     * @return Agreement
     */
    public function setStudent(User $student)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return User
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set workTutor
     *
     * @param User $workTutor
     *
     * @return Agreement
     */
    public function setWorkTutor(User $workTutor = null)
    {
        $this->workTutor = $workTutor;

        return $this;
    }

    /**
     * Get workTutor
     *
     * @return User
     */
    public function getWorkTutor()
    {
        return $this->workTutor;
    }

    /**
     * Set educationalTutor
     *
     * @param User $educationalTutor
     *
     * @return Agreement
     */
    public function setEducationalTutor(User $educationalTutor = null)
    {
        $this->educationalTutor = $educationalTutor;

        return $this;
    }

    /**
     * Get educationalTutor
     *
     * @return User
     */
    public function getEducationalTutor()
    {
        return $this->educationalTutor;
    }

    /**
     * Set workcenter
     *
     * @param Workcenter $workcenter
     *
     * @return Agreement
     */
    public function setWorkcenter(Workcenter $workcenter = null)
    {
        $this->workcenter = $workcenter;

        return $this;
    }

    /**
     * Get workcenter
     *
     * @return Workcenter
     */
    public function getWorkcenter()
    {
        return $this->workcenter;
    }

    /**
     * Add activity
     *
     * @param Activity $activity
     *
     * @return Agreement
     */
    public function addActivity(Activity $activity)
    {
        $this->activities[] = $activity;

        return $this;
    }

    /**
     * Remove activity
     *
     * @param Activity $activity
     */
    public function removeActivity(Activity $activity)
    {
        $this->activities->removeElement($activity);
    }

    /**
     * Get activities
     *
     * @return Collection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Add workday
     *
     * @param Workday $workday
     *
     * @return Agreement
     */
    public function addWorkday(Workday $workday)
    {
        $this->workdays[] = $workday;

        return $this;
    }

    /**
     * Remove workday
     *
     * @param Workday $workday
     */
    public function removeWorkday(Workday $workday)
    {
        $this->workdays->removeElement($workday);
    }

    /**
     * Get workdays
     *
     * @return Collection
     */
    public function getWorkdays()
    {
        return $this->workdays;
    }

    /**
     * Set report
     *
     * @param Report $report
     *
     * @return Agreement
     */
    public function setReport(Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return Report
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Get total hours
     *
     * @return float
     */
    public function getTotalHours()
    {
        $total = 0.0;

        /** @var Workday $workday */
        foreach ($this->getWorkdays() as $workday) {
            $total += $workday->getHours();
        }

        return $total;
    }

    /**
     * Get tracked hours
     *
     * @return float
     */
    public function getTrackedHours()
    {
        $total = 0.0;

        /** @var Workday $workday */
        foreach ($this->getWorkdays() as $workday) {
            $total += $workday->getTrackedHours();
        }

        return $total;
    }


    /**
     * Set quarter
     *
     * @param integer $quarter
     *
     * @return Agreement
     */
    public function setQuarter($quarter)
    {
        $this->quarter = $quarter;

        return $this;
    }

    /**
     * Get quarter
     *
     * @return integer
     */
    public function getQuarter()
    {
        return $this->quarter;
    }
}
