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
 * @ORM\Entity(repositoryClass="ActivityRepository")
 */
class Activity
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
     * @ORM\ManyToOne(targetEntity="LearningOutcome", inversedBy="activities")
     * @ORM\JoinColumn(nullable=false)
     * @var LearningOutcome
     */
    protected $learningOutcome;

    /**
     * @ORM\ManyToMany(targetEntity="Criterion")
     * @var Collection
     */
    protected $criteria;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    protected $orderNr;

    /**
     * Constructor
     */
    public function __construct()
    {
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
     * @return Activity
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
     * @return Activity
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
     * @return Activity
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
     * @return Activity
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
     * Set learningOutcome
     *
     * @param LearningOutcome $learningOutcome
     *
     * @return Activity
     */
    public function setLearningOutcome(LearningOutcome $learningOutcome)
    {
        $this->learningOutcome = $learningOutcome;

        return $this;
    }

    /**
     * Get learningOutcome
     *
     * @return LearningOutcome
     */
    public function getLearningOutcome()
    {
        return $this->learningOutcome;
    }

    /**
     * Add criterion
     *
     * @param Criterion $criterion
     *
     * @return Activity
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
     * @return Collection
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
