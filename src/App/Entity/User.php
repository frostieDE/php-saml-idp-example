<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="users")
 */
class User implements UserInterface {
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     * @Assert\Length(min="5", max="64")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $firstname;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="json_array")
     */
    private $roles = [ 'ROLE_USER' ];

    /**
     * @ORM\ManyToMany(targetEntity="ServiceProvider")
     * @ORM\JoinTable(name="user_providers",
     *  joinColumns={@ORM\JoinColumn(name="user", referencedColumnName="id")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="provider", referencedColumnName="id")}
     * )
     */
    private $allowedServiceProviders;

    public function __construct() {
        $this->allowedServiceProviders = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname() {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return User
     */
    public function setFirstname($firstname) {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname() {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return User
     */
    public function setLastname($lastname) {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * @param string[] $roles
     * @return User
     */
    public function setRoles(array $roles) {
        $this->roles = $roles;
        return $this;
    }

    public function addAllowedServiceProvider(ServiceProvider $serviceProvider) {
        $this->allowedServiceProviders->add($serviceProvider);
    }

    public function removeAllowedServiceProvider(ServiceProvider $serviceProvider) {
        $this->allowedServiceProviders->removeElement($serviceProvider);
    }

    /**
     * @return ArrayCollection
     */
    public function getAllowedServiceProviders() {
        return $this->allowedServiceProviders;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function getSalt() { }

    /**
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }

    public function eraseCredentials() { }
}