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
class Report
{
    const GRADE_NEGATIVE = 0;
    const GRADE_POSITIVE = 1;
    const GRADE_EXCELENT = 2;

    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Agreement", inversedBy="report")
     * @var Agreement
     */
    protected $agreement;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    protected $workActivities;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $professionalCompetence;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $organizationalCompetence;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $relationalCompetence;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    protected $contingencyResponse;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @var string
     */
    protected $proposedChanges;

    /**
     * @ORM\Column(type="date")
     * @var \DateTime
     */
    protected $signDate;

    /**
     * Set workActivities
     *
     * @param string $workActivities
     *
     * @return Report
     */
    public function setWorkActivities($workActivities)
    {
        $this->workActivities = $workActivities;

        return $this;
    }

    /**
     * Get workActivities
     *
     * @return string
     */
    public function getWorkActivities()
    {
        return $this->workActivities;
    }

    /**
     * Set professionalCompetence
     *
     * @param integer $professionalCompetence
     *
     * @return Report
     */
    public function setProfessionalCompetence($professionalCompetence)
    {
        $this->professionalCompetence = $professionalCompetence;

        return $this;
    }

    /**
     * Get professionalCompetence
     *
     * @return integer
     */
    public function getProfessionalCompetence()
    {
        return $this->professionalCompetence;
    }

    /**
     * Set organizationalCompetence
     *
     * @param integer $organizationalCompetence
     *
     * @return Report
     */
    public function setOrganizationalCompetence($organizationalCompetence)
    {
        $this->organizationalCompetence = $organizationalCompetence;

        return $this;
    }

    /**
     * Get organizationalCompetence
     *
     * @return integer
     */
    public function getOrganizationalCompetence()
    {
        return $this->organizationalCompetence;
    }

    /**
     * Set relationalCompetence
     *
     * @param integer $relationalCompetence
     *
     * @return Report
     */
    public function setRelationalCompetence($relationalCompetence)
    {
        $this->relationalCompetence = $relationalCompetence;

        return $this;
    }

    /**
     * Get relationalCompetence
     *
     * @return integer
     */
    public function getRelationalCompetence()
    {
        return $this->relationalCompetence;
    }

    /**
     * Set contingencyResponse
     *
     * @param integer $contingencyResponse
     *
     * @return Report
     */
    public function setContingencyResponse($contingencyResponse)
    {
        $this->contingencyResponse = $contingencyResponse;

        return $this;
    }

    /**
     * Get contingencyResponse
     *
     * @return integer
     */
    public function getContingencyResponse()
    {
        return $this->contingencyResponse;
    }

    /**
     * Set proposedChanges
     *
     * @param string $proposedChanges
     *
     * @return Report
     */
    public function setProposedChanges($proposedChanges)
    {
        $this->proposedChanges = $proposedChanges;

        return $this;
    }

    /**
     * Get proposedChanges
     *
     * @return string
     */
    public function getProposedChanges()
    {
        return $this->proposedChanges;
    }

    /**
     * Set agreement
     *
     * @param \AppBundle\Entity\Agreement $agreement
     *
     * @return Report
     */
    public function setAgreement(\AppBundle\Entity\Agreement $agreement)
    {
        $this->agreement = $agreement;

        return $this;
    }

    /**
     * Get agreement
     *
     * @return \AppBundle\Entity\Agreement
     */
    public function getAgreement()
    {
        return $this->agreement;
    }

    /**
     * Set signDate
     *
     * @param \DateTime $signDate
     *
     * @return Report
     */
    public function setSignDate($signDate)
    {
        $this->signDate = $signDate;

        return $this;
    }

    /**
     * Get signDate
     *
     * @return \DateTime
     */
    public function getSignDate()
    {
        return $this->signDate;
    }
}
