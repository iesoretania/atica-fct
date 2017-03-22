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
 * @ORM\Entity(repositoryClass="LearningOutcomeRepository")
 */
class LearningOutcome
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Training", inversedBy="learningOutcomes")
     * @ORM\JoinColumn(nullable=false)
     * @var Training
     */
    protected $training;

    /**
     * @ORM\OneToMany(targetEntity="Activity", mappedBy="learningOutcome")
     * @var Collection
     */
    protected $activities;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    protected $orderNr;

    /**
     * @ORM\OneToMany(targetEntity="Criterion", mappedBy="learningOutcome")
     * @var Collection
     */
    protected $criteria;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->criteria = new ArrayCollection();
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
     * @return string
     */
    public function __toString()
    {
        return $this->getCode() ? $this->getCode() . ': ' . $this->getName() : $this->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return LearningOutcome
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
     * Set description
     *
     * @param string $description
     *
     * @return LearningOutcome
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set order
     *
     * @param integer $orderNr
     *
     * @return LearningOutcome
     */
    public function setOrderNr($orderNr)
    {
        $this->orderNr = $orderNr;

        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrderNr()
    {
        return $this->orderNr;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return LearningOutcome
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
     * Add activity
     *
     * @param Activity $activity
     *
     * @return LearningOutcome
     */
    public function addActivity(Activity $activity)
    {
        $this->activities[] = $activity;

        return $this;
    }

    /**
     * Remove activity
     *
     * @param Activity $activity
     */
    public function removeActivity(Activity $activity)
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
     * Set training
     *
     * @param Training $training
     *
     * @return LearningOutcome
     */
    public function setTraining(Training $training)
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
     * Add criterion
     *
     * @param Criterion $criterion
     *
     * @return LearningOutcome
     */
    public function addCriterion(Criterion $criterion)
    {
        $this->criteria[] = $criterion;

        return $this;
    }

    /**
     * Remove criterion
     *
     * @param Criterion $criterion
     */
    public function removeCriterion(Criterion $criterion)
    {
        $this->criteria->removeElement($criterion);
    }

    /**
     * Get criteria
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
