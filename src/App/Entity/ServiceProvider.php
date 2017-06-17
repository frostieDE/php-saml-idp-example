<?php

namespace App\Entity;

use App\Validator\X509Certificate;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="service_providers")
 * @UniqueEntity(fields={"entityId"})
 */
class ServiceProvider {

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=191, unique=true)
     * @Assert\NotBlank()
     */
    private $entityId;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @X509Certificate()
     */
    private $certificate;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $callbackUrl;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityId() {
        return $this->entityId;
    }

    /**
     * @param string $entityId
     * @return ServiceProvider
     */
    public function setEntityId($entityId) {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ServiceProvider
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param string $description
     * @return ServiceProvider
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getCertificate() {
        return $this->certificate;
    }

    /**
     * @param string $certificate
     * @return ServiceProvider
     */
    public function setCertificate($certificate) {
        $this->certificate = $certificate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCallbackUrl() {
        return $this->callbackUrl;
    }

    /**
     * @param string $callbackUrl
     * @return ServiceProvider
     */
    public function setCallbackUrl($callbackUrl) {
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

}