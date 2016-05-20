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
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="classroom")
 * @UniqueEntity(fields={"name"})
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Training", inversedBy="groups")
     * @var Training
     */
    protected $training;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="tutorizedGroups")
     * @ORM\JoinTable(name="tutorized_groups")
     * @var Collection
     */
    protected $tutors;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="studentGroup")
     * @var Collection
     */
    protected $students;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tutors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->students = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName() ? $this->getName() : '';
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
     * Set name
     *
     * @param string $name
     *
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set training
     *
     * @param Training $training
     *
     * @return Group
     */
    public function setTraining(Training $training = null)
    {
        $this->training = $training;

        return $this;
    }

    /**
     * Get training
     *
     * @return Training
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * Add tutor
     *
     * @param User $tutor
     *
     * @return Group
     */
    public function addTutor(User $tutor)
    {
        $this->tutors[] = $tutor;

        return $this;
    }

    /**
     * Remove tutor
     *
     * @param User $tutor
     */
    public function removeTutor(User $tutor)
    {
        $this->tutors->removeElement($tutor);
    }

    /**
     * Get tutors
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTutors()
    {
        return $this->tutors;
    }

    /**
     * Add student
     *
     * @param User $student
     *
     * @return Group
     */
    public function addStudent(User $student)
    {
        $this->students[] = $student;

        return $this;
    }

    /**
     * Remove student
     *
     * @param User $student
     */
    public function removeStudent(User $student)
    {
        $this->students->removeElement($student);
    }

    /**
     * Get students
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStudents()
    {
        return $this->students;
    }
}
