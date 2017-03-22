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
 * @ORM\Entity
 */
class Criterion
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
     * @ORM\ManyToOne(targetEntity="LearningOutcome", inversedBy="criteria")
     * @ORM\JoinColumn(nullable=true)
     * @var LearningOutcome
     */
    protected $learningOutcome;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int
     */
    protected $orderNr;

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
     * @return Criterion
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
     * @return Criterion
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
     * @return Criterion
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
     * @return Criterion
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
     * @param \AppBundle\Entity\LearningOutcome $learningOutcome
     *
     * @return Criterion
     */
    public function setLearningOutcome(\AppBundle\Entity\LearningOutcome $learningOutcome = null)
    {
        $this->learningOutcome = $learningOutcome;

        return $this;
    }

    /**
     * Get learningOutcome
     *
     * @return \AppBundle\Entity\LearningOutcome
     */
    public function getLearningOutcome()
    {
        return $this->learningOutcome;
    }
}
