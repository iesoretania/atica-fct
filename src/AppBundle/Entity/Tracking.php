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
 * @ORM\Entity(repositoryClass="TrackingRepository")
 */
class Tracking
{
    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Workday", inversedBy="trackingActivities")
     * @var Workday
     */
    protected $workday;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Activity")
     * @var Activity
     */
    protected $activity;

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
     * Set notes
     *
     * @param string $notes
     *
     * @return Tracking
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
     * @return Tracking
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
     * Set workday
     *
     * @param Workday $workday
     *
     * @return Tracking
     */
    public function setWorkday(Workday $workday)
    {
        $this->workday = $workday;
        $workday->addTrackingActivity($this);

        return $this;
    }

    /**
     * Get workday
     *
     * @return Workday
     */
    public function getWorkday()
    {
        return $this->workday;
    }

    /**
     * Set activity
     *
     * @param Activity $activity
     *
     * @return Tracking
     */
    public function setActivity(Activity $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return Activity
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
