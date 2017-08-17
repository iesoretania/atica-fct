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

/**
 * @ORM\Entity(repositoryClass="VisitRepository")
 */
class Visit
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="visits")
     * @ORM\JoinColumn(nullable=false)
     * @var User
     */
    protected $tutor;

    /**
     * @ORM\ManyToMany(targetEntity="Agreement")
     * @var Collection
     */
    protected $agreements;

    /**
     * @ORM\ManyToOne(targetEntity="Workcenter", inversedBy="visits")
     * @ORM\JoinColumn(nullable=false)
     * @var Workcenter
     */
    protected $workcenter;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $notes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->agreements = new ArrayCollection();
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
     * Set when
     *
     * @param \DateTime $date
     *
     * @return Visit
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get when
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
     * @return Visit
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
     * @param User $tutor
     *
     * @return Visit
     */
    public function setTutor(User $tutor)
    {
        $this->tutor = $tutor;

        return $this;
    }

    /**
     * Get teacher
     *
     * @return User
     */
    public function getTutor()
    {
        return $this->tutor;
    }

    /**
     * Set workcenter
     *
     * @param Workcenter $workcenter
     *
     * @return Visit
     */
    public function setWorkcenter(Workcenter $workcenter)
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
     * Add agreement
     *
     * @param User $agreement
     *
     * @return Visit
     */
    public function addStudent(User $agreement)
    {
        $this->agreements[] = $agreement;

        return $this;
    }

    /**
     * Remove agreement
     *
     * @param User $agreement
     */
    public function removeStudent(User $agreement)
    {
        $this->agreements->removeElement($agreement);
    }

    /**
     * Get agreements
     *
     * @return Collection
     */
    public function getAgreements()
    {
        return $this->agreements;
    }
}
