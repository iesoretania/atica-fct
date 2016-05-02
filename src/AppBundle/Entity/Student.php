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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @UniqueEntity(fields={"code"})
 */
class Student
{
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Person")
     * @ORM\JoinColumn(name="id")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $code;

    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="students")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $group;

    /**
     * @ORM\OneToMany(targetEntity="Agreement", mappedBy="student")
     */
    protected $agreements;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->agreements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Student
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set id
     *
     * @param \AppBundle\Entity\Person $id
     *
     * @return Student
     */
    public function setId(\AppBundle\Entity\Person $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \AppBundle\Entity\Person
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Group $group
     *
     * @return Student
     */
    public function setGroup(\AppBundle\Entity\Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Add agreement
     *
     * @param \AppBundle\Entity\Agreement $agreement
     *
     * @return Student
     */
    public function addAgreement(\AppBundle\Entity\Agreement $agreement)
    {
        $this->agreements[] = $agreement;

        return $this;
    }

    /**
     * Remove agreement
     *
     * @param \AppBundle\Entity\Agreement $agreement
     */
    public function removeAgreement(\AppBundle\Entity\Agreement $agreement)
    {
        $this->agreements->removeElement($agreement);
    }

    /**
     * Get agreements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAgreements()
    {
        return $this->agreements;
    }
}
