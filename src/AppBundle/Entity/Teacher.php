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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Teacher
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="id")
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="tutors")
     * @var Collection
     */
    protected $tutorizedGroups;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="head")
     * @var Collection
     */
    protected $directs;

    /**
     * @ORM\OneToMany(targetEntity="Agreement", mappedBy="educationalTutor")
     * @var Collection
     */
    protected $agreements;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tutorizedGroups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->directs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->agreements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param Person $id
     *
     * @return Teacher
     */
    public function setId(Person $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return Person
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add tutorizedGroup
     *
     * @param Group $tutorizedGroup
     *
     * @return Teacher
     */
    public function addTutorizedGroup(Group $tutorizedGroup)
    {
        $this->tutorizedGroups[] = $tutorizedGroup;

        return $this;
    }

    /**
     * Remove tutorizedGroup
     *
     * @param Group $tutorizedGroup
     */
    public function removeTutorizedGroup(Group $tutorizedGroup)
    {
        $this->tutorizedGroups->removeElement($tutorizedGroup);
    }

    /**
     * Get tutorizedGroups
     *
     * @return Collection
     */
    public function getTutorizedGroups()
    {
        return $this->tutorizedGroups;
    }

    /**
     * Add direct
     *
     * @param Department $direct
     *
     * @return Teacher
     */
    public function addDirect(Department $direct)
    {
        $this->directs[] = $direct;

        return $this;
    }

    /**
     * Remove direct
     *
     * @param Department $direct
     */
    public function removeDirect(Department $direct)
    {
        $this->directs->removeElement($direct);
    }

    /**
     * Get directs
     *
     * @return Collection
     */
    public function getDirects()
    {
        return $this->directs;
    }

    /**
     * Add agreement
     *
     * @param Agreement $agreement
     *
     * @return Teacher
     */
    public function addAgreement(Agreement $agreement)
    {
        $this->agreements[] = $agreement;

        return $this;
    }

    /**
     * Remove agreement
     *
     * @param Agreement $agreement
     */
    public function removeAgreement(Agreement $agreement)
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
