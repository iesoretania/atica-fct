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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="WorkdayRepository")
 * @UniqueEntity(fields={"agreement", "date"})
 */
class Workday
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Agreement", inversedBy="workdays")
     * @var Agreement
     */
    protected $agreement;

    /**
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $notes;

    /**
     * @ORM\Column(type="float")
     * @var float
     */
    protected $hours;

    /**
     * @ORM\OneToMany(targetEntity="Tracking", mappedBy="workday", fetch="EAGER")
     * @var Collection
     */
    protected $trackingActivities;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $locked;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Workday
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
    
    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Workday
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set hours
     *
     * @param float $hours
     *
     * @return Workday
     */
    public function setHours($hours)
    {
        $this->hours = $hours;

        return $this;
    }

    /**
     * Get hours
     *
     * @return float
     */
    public function getHours()
    {
        return $this->hours;
    }

    /**
     * Set agreement
     *
     * @param Agreement $agreement
     *
     * @return Workday
     */
    public function setAgreement(Agreement $agreement = null)
    {
        $this->agreement = $agreement;

        return $this;
    }

    /**
     * Get agreement
     *
     * @return Agreement
     */
    public function getAgreement()
    {
        return $this->agreement;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->trackingActivities = new ArrayCollection();
        $this->setLocked(false);
    }

    /**
     * Add trackingActivity
     *
     * @param Tracking $trackingActivity
     *
     * @return Workday
     */
    public function addTrackingActivity(Tracking $trackingActivity)
    {
        $this->trackingActivities[] = $trackingActivity;

        return $this;
    }

    /**
     * Remove trackingActivity
     *
     * @param Tracking $trackingActivity
     */
    public function removeTrackingActivity(Tracking $trackingActivity)
    {
        $this->trackingActivities->removeElement($trackingActivity);
    }

    /**
     * Get trackingActivities
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTrackingActivities()
    {
        return $this->trackingActivities;
    }

    /**
     * @return float
     */
    public function getTrackedHours()
    {
        $hours = 0.0;
        /** @var Tracking $track */
        foreach ($this->getTrackingActivities() as $track) {
            $hours += $track->getHours();
        }

        return $hours;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return Workday
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Is locked
     *
     * @return boolean
     */
    public function isLocked()
    {
        return $this->locked;
    }
}
