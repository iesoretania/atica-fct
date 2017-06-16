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
 * @ORM\Entity(repositoryClass="ExpenseRepository")
 */
class Expense
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="expenses")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    protected $teacher;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $route;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @var float
     */
    protected $distance;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $reviewed;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @var bool
     */
    protected $paid;
    
    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $notes;


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
     * @return Expense
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
     * Set route
     *
     * @param string $route
     *
     * @return Expense
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set distance
     *
     * @param float $distance
     *
     * @return Expense
     */
    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    /**
     * Get distance
     *
     * @return float
     */
    public function getDistance()
    {
        return $this->distance;
    }

    /**
     * Set reviewed
     *
     * @param boolean $reviewed
     *
     * @return Expense
     */
    public function setReviewed($reviewed)
    {
        $this->reviewed = $reviewed;

        return $this;
    }

    /**
     * Get reviewed
     *
     * @return boolean
     */
    public function isReviewed()
    {
        return $this->reviewed;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return Expense
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        // si se ha pagado, ha sido revisado
        if ($paid) {
            $this->setReviewed(true);
        }

        return $this;
    }

    /**
     * Get paid
     *
     * @return boolean
     */
    public function isPaid()
    {
        return $this->paid;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Expense
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
     * Set teacher
     *
     * @param User $teacher
     *
     * @return Expense
     */
    public function setTeacher(User $teacher)
    {
        $this->teacher = $teacher;

        return $this;
    }

    /**
     * Get teacher
     *
     * @return User
     */
    public function getTeacher()
    {
        return $this->teacher;
    }
}
