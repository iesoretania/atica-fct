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
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @UniqueEntity("loginUsername")
 */
class User extends Person implements UserInterface, \Serializable
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
     * @Assert\Regex(pattern="/[@ ]{1,}/", match=false, message="login_username.invalid_chars", htmlPattern=false)
     * @Assert\Length(min=5)
     * @var string
     */
    protected $loginUsername;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min=7)
     * @var string
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    protected $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $tokenValidity;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $enabled;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $globalAdministrator;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    protected $lastLogin;

    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="students")
     *
     * @var Group
     */
    protected $studentGroup;

    /**
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="tutors")
     * @ORM\JoinTable(name="tutorized_groups")
     *
     * @var Collection
     */
    protected $tutorizedGroups;

    /**
     * @ORM\OneToMany(targetEntity="Department", mappedBy="head")
     *
     * @var Collection
     */
    protected $directs;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->tutorizedGroups = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get login username
     *
     * @return string
     */
    public function getLoginUsername()
    {
        return $this->loginUsername;
    }

    /**
     * Set login username
     *
     * @param string $loginUsername
     *
     * @return User
     */
    public function setLoginUsername($loginUsername)
    {
        $this->loginUsername = $loginUsername;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get tokenValidity
     *
     * @return \DateTime
     */
    public function getTokenValidity()
    {
        return $this->tokenValidity;
    }

    /**
     * Set tokenValidity
     *
     * @param \DateTime $tokenValidity
     *
     * @return User
     */
    public function setTokenValidity($tokenValidity)
    {
        $this->tokenValidity = $tokenValidity;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get globalAdministrator
     *
     * @return boolean
     */
    public function isGlobalAdministrator()
    {
        return $this->globalAdministrator;
    }

    /**
     * Set globalAdministrator
     *
     * @param boolean $globalAdministrator
     *
     * @return User
     */
    public function setGlobalAdministrator($globalAdministrator)
    {
        $this->globalAdministrator = $globalAdministrator;

        return $this;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->loginUsername,
            $this->password
        ));
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->loginUsername,
            $this->password
        ) = unserialize($serialized);
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /**
     * Returns the roles granted to the user.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        // Always return ROLE_USER
        $roles = [new Role('ROLE_USER')];

        if ($this->isGlobalAdministrator()) {
            $roles[] = new Role('ROLE_ADMIN');
        }

        return $roles;
    }

    /**
     * Set lastLogin
     *
     * @param \DateTime $lastLogin
     *
     * @return User
     */
    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    /**
     * Get lastLogin
     *
     * @return \DateTime
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * Set group
     *
     * @param Group $group
     *
     * @return User
     */
    public function setStudentGroup(Group $group = null)
    {
        $this->studentGroup = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return Group
     */
    public function getStudentGroup()
    {
        return $this->studentGroup;
    }

    /**
     * Add tutorizedGroup
     *
     * @param Group $tutorizedGroup
     *
     * @return User
     */
    public function addTutorizedGroup(Group $tutorizedGroup)
    {
        if (false === $this->tutorizedGroups->contains($tutorizedGroup)) {
            $this->tutorizedGroups[] = $tutorizedGroup;
            $tutorizedGroup->addTutor($this);
        }

        return $this;
    }

    /**
     * Remove tutorizedGroup
     *
     * @param Group $tutorizedGroup
     */
    public function removeTutorizedGroup(Group $tutorizedGroup)
    {
        if (true === $this->tutorizedGroups->contains($tutorizedGroup)) {
            $this->tutorizedGroups->removeElement($tutorizedGroup);
            $tutorizedGroup->removeTutor($this);
        }
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
     * @return User
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
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // comprobar si se ha especificado al menos el nombre de usuario o el correo electrónico
        if (!$this->getLoginUsername() && !$this->getEmail()) {
            $context->buildViolation('user.id.not_found')
                ->atPath('loginUsername')
                ->addViolation();
            $context->buildViolation('user.id.not_found')
                ->atPath('email')
                ->addViolation();
        }
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getLoginUsername() ?: $this->getEmail();
    }
}
