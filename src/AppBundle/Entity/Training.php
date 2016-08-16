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
 * @ORM\Entity
 * @UniqueEntity(fields={"name"})
 */
class Training
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
     * @ORM\Column(type="string")
     * @var string
     */
    protected $stage;

    /**
     * @ORM\OneToMany(targetEntity="Group", mappedBy="training")
     * @var Collection
     */
    protected $groups;

    /**
     * @ORM\ManyToOne(targetEntity="Department", inversedBy="trainings")
     * @ORM\JoinColumn(nullable=false)
     * @var Department
     */
    protected $department;

    /**
     * @ORM\OneToMany(targetEntity="LearningOutcome", mappedBy="training")
     * @var Collection
     */
    protected $learningOutcomes;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $programHours;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new ArrayCollection();
        $this->learningOutcomes = new ArrayCollection();
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
     * @return Training
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
     * Add group
     *
     * @param Group $group
     *
     * @return Training
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param Group $group
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);
    }

    /**
     * Get groups
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set department
     *
     * @param Department $department
     *
     * @return Training
     */
    public function setDepartment(Department $department)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set programHours
     *
     * @param integer $programHours
     *
     * @return Training
     */
    public function setProgramHours($programHours)
    {
        $this->programHours = $programHours;

        return $this;
    }

    /**
     * Get programHours
     *
     * @return integer
     */
    public function getProgramHours()
    {
        return $this->programHours;
    }

    /**
     * Set stage
     *
     * @param string $stage
     *
     * @return Training
     */
    public function setStage($stage)
    {
        $this->stage = $stage;

        return $this;
    }

    /**
     * Get stage
     *
     * @return string
     */
    public function getStage()
    {
        return $this->stage;
    }

    /**
     * Add learningOutcome
     *
     * @param LearningOutcome $learningOutcome
     *
     * @return Training
     */
    public function addLearningOutcome(LearningOutcome $learningOutcome)
    {
        $this->learningOutcomes[] = $learningOutcome;

        return $this;
    }

    /**
     * Remove learningOutcome
     *
     * @param LearningOutcome $learningOutcome
     */
    public function removeLearningOutcome(LearningOutcome $learningOutcome)
    {
        $this->learningOutcomes->removeElement($learningOutcome);
    }

    /**
     * Get learningOutcomes
     *
     * @return Collection
     */
    public function getLearningOutcomes()
    {
        return $this->learningOutcomes;
    }

    /**
     * Get activities
     *
     * @return Collection
     */
    public function getActivities()
    {
        $activities = new ArrayCollection();
        /** @var LearningOutcome $learningOutcome */
        foreach ($this->getLearningOutcomes() as $learningOutcome) {
            /** @var Activity $activity */
            foreach ($learningOutcome->getActivities() as $activity) {
                $activities->add($activity);
            }
        }

        return $activities;
    }
}
