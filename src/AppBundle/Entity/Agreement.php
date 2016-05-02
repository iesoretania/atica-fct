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

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Agreement
{
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
     * @var string
     */
    protected $toDate;

    /**
     * @ORM\ManyToOne(targetEntity="Student", inversedBy="agreements")
     * @ORM\JoinColumn(nullable=false)
     * @var Student
     */
    protected $student;

    /**
     * @ORM\ManyToOne(targetEntity="Person")
     * @ORM\JoinColumn(nullable=false)
     * @var Person
     */
    protected $workTutor;

    /**
     * @ORM\ManyToOne(targetEntity="Teacher", inversedBy="agreements")
     * @ORM\JoinColumn(nullable=false)
     * @var Teacher
     */
    protected $educationalTutor;

    /**
     * @ORM\ManyToOne(targetEntity="Workcenter", inversedBy="agreements")
     * @ORM\JoinColumn(nullable=false)
     * @var Workcenter
     */
    protected $workcenter;

    /**
     * @ORM\ManyToMany(targetEntity="Activity")
     * @var Activity[]
     */
    protected $activities;

    /**
     * @ORM\OneToMany(targetEntity="Visit", mappedBy="agreement")
     * @var Visit[]
     */
    protected $visits;

    /**
     * @ORM\OneToMany(targetEntity="Workday", mappedBy="agreement")
     * @var Workday[]
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
        $this->activities = new \Doctrine\Common\Collections\ArrayCollection();
        $this->visits = new \Doctrine\Common\Collections\ArrayCollection();
        $this->workdays = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \AppBundle\Entity\Student $student
     *
     * @return Agreement
     */
    public function setStudent(\AppBundle\Entity\Student $student)
    {
        $this->student = $student;

        return $this;
    }

    /**
     * Get student
     *
     * @return \AppBundle\Entity\Student
     */
    public function getStudent()
    {
        return $this->student;
    }

    /**
     * Set workTutor
     *
     * @param \AppBundle\Entity\Person $workTutor
     *
     * @return Agreement
     */
    public function setWorkTutor(\AppBundle\Entity\Person $workTutor)
    {
        $this->workTutor = $workTutor;

        return $this;
    }

    /**
     * Get workTutor
     *
     * @return \AppBundle\Entity\Person
     */
    public function getWorkTutor()
    {
        return $this->workTutor;
    }

    /**
     * Set educationalTutor
     *
     * @param \AppBundle\Entity\Teacher $educationalTutor
     *
     * @return Agreement
     */
    public function setEducationalTutor(\AppBundle\Entity\Teacher $educationalTutor)
    {
        $this->educationalTutor = $educationalTutor;

        return $this;
    }

    /**
     * Get educationalTutor
     *
     * @return \AppBundle\Entity\Teacher
     */
    public function getEducationalTutor()
    {
        return $this->educationalTutor;
    }

    /**
     * Set workcenter
     *
     * @param \AppBundle\Entity\Workcenter $workcenter
     *
     * @return Agreement
     */
    public function setWorkcenter(\AppBundle\Entity\Workcenter $workcenter)
    {
        $this->workcenter = $workcenter;

        return $this;
    }

    /**
     * Get workcenter
     *
     * @return \AppBundle\Entity\Workcenter
     */
    public function getWorkcenter()
    {
        return $this->workcenter;
    }

    /**
     * Add activity
     *
     * @param \AppBundle\Entity\Activity $activity
     *
     * @return Agreement
     */
    public function addActivity(\AppBundle\Entity\Activity $activity)
    {
        $this->activities[] = $activity;

        return $this;
    }

    /**
     * Remove activity
     *
     * @param \AppBundle\Entity\Activity $activity
     */
    public function removeActivity(\AppBundle\Entity\Activity $activity)
    {
        $this->activities->removeElement($activity);
    }

    /**
     * Get activities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivities()
    {
        return $this->activities;
    }

    /**
     * Add visit
     *
     * @param \AppBundle\Entity\Visit $visit
     *
     * @return Agreement
     */
    public function addVisit(\AppBundle\Entity\Visit $visit)
    {
        $this->visits[] = $visit;

        return $this;
    }

    /**
     * Remove visit
     *
     * @param \AppBundle\Entity\Visit $visit
     */
    public function removeVisit(\AppBundle\Entity\Visit $visit)
    {
        $this->visits->removeElement($visit);
    }

    /**
     * Get visits
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVisits()
    {
        return $this->visits;
    }

    /**
     * Add workday
     *
     * @param \AppBundle\Entity\Workday $workday
     *
     * @return Agreement
     */
    public function addWorkday(\AppBundle\Entity\Workday $workday)
    {
        $this->workdays[] = $workday;

        return $this;
    }

    /**
     * Remove workday
     *
     * @param \AppBundle\Entity\Workday $workday
     */
    public function removeWorkday(\AppBundle\Entity\Workday $workday)
    {
        $this->workdays->removeElement($workday);
    }

    /**
     * Get workdays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getWorkdays()
    {
        return $this->workdays;
    }

    /**
     * Set report
     *
     * @param \AppBundle\Entity\Report $report
     *
     * @return Agreement
     */
    public function setReport(\AppBundle\Entity\Report $report = null)
    {
        $this->report = $report;

        return $this;
    }

    /**
     * Get report
     *
     * @return \AppBundle\Entity\Report
     */
    public function getReport()
    {
        return $this->report;
    }
}
