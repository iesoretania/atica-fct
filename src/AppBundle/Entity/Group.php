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
 * @ORM\Table(name="student_group")
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
     * @ORM\ManyToMany(targetEntity="Teacher", inversedBy="tutorizedGroups")
     * @var Teacher[]
     */
    protected $tutors;

    /**
     * @ORM\OneToMany(targetEntity="Student", mappedBy="group")
     * @var Student[]
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
     * @param \AppBundle\Entity\Training $training
     *
     * @return Group
     */
    public function setTraining(\AppBundle\Entity\Training $training = null)
    {
        $this->training = $training;

        return $this;
    }

    /**
     * Get training
     *
     * @return \AppBundle\Entity\Training
     */
    public function getTraining()
    {
        return $this->training;
    }

    /**
     * Add tutor
     *
     * @param \AppBundle\Entity\Teacher $tutor
     *
     * @return Group
     */
    public function addTutor(\AppBundle\Entity\Teacher $tutor)
    {
        $this->tutors[] = $tutor;

        return $this;
    }

    /**
     * Remove tutor
     *
     * @param \AppBundle\Entity\Teacher $tutor
     */
    public function removeTutor(\AppBundle\Entity\Teacher $tutor)
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
     * @param \AppBundle\Entity\Student $student
     *
     * @return Group
     */
    public function addStudent(\AppBundle\Entity\Student $student)
    {
        $this->students[] = $student;

        return $this;
    }

    /**
     * Remove student
     *
     * @param \AppBundle\Entity\Student $student
     */
    public function removeStudent(\AppBundle\Entity\Student $student)
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