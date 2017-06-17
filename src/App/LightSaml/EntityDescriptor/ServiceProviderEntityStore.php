<?php

namespace App\LightSaml\EntityDescriptor;

use App\Entity\ServiceProvider;
use Doctrine\ORM\EntityManager;
use LightSaml\Credential\X509Certificate;
use LightSaml\Model\Metadata\AssertionConsumerService;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Model\Metadata\SpSsoDescriptor;
use LightSaml\SamlConstants;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;

/**
 * This store retrieves information about registered service providers from the database
 */
class ServiceProviderEntityStore implements EntityDescriptorStoreInterface {

    private $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    public function get($entityId) {
        $serviceProvider = $this->entityManager
            ->getRepository(ServiceProvider::class)
            ->findOneByEntityId($entityId);

        if($serviceProvider === null) {
            return null;
        }

        return $this->getEntityDescriptor($serviceProvider);
    }

    public function has($entityId) {
        return $this->get($entityId) !== null;
    }

    public function all() {
        /** @var EntityDescriptor[] $all */
        $all = [ ];

        /** @var ServiceProvider[] $serviceProviders */
        $serviceProviders = $this->entityManager
            ->getRepository(ServiceProvider::class)
            ->findAll();

        foreach ($serviceProviders as $serviceProvider) {
            $all[] = $this->getEntityDescriptor($serviceProvider);
        }

        return $all;
    }

    /**
     * Converts a ServiceProvider entity into an entity descriptor for further use within the LightSAML library
     *
     * @param ServiceProvider $serviceProvider
     * @return EntityDescriptor
     */
    public function getEntityDescriptor(ServiceProvider $serviceProvider) {
        $entityDescriptor = new EntityDescriptor($serviceProvider->getEntityId());
        $spDescriptor = new SpSsoDescriptor();

        $spDescriptor->addKeyDescriptor($this->getKeyDescriptor($serviceProvider, KeyDescriptor::USE_SIGNING));
        $spDescriptor->addKeyDescriptor($this->getKeyDescriptor($serviceProvider, KeyDescriptor::USE_ENCRYPTION));

        $consumerService = new AssertionConsumerService($serviceProvider->getCallbackUrl());
        $consumerService->setBinding(SamlConstants::BINDING_SAML2_HTTP_POST);
        $spDescriptor->addAssertionConsumerService($consumerService);

        $entityDescriptor->addItem($spDescriptor);
        return $entityDescriptor;
    }

    /**
     * @param ServiceProvider $serviceProvider
     * @param string $use
     * @return KeyDescriptor
     */
    private function getKeyDescriptor(ServiceProvider $serviceProvider, $use) {
        $keyDescriptor = new KeyDescriptor();
        $keyDescriptor->setUse($use);
        $certificate = new X509Certificate();
        $certificate->loadPem($serviceProvider->getCertificate());
        $keyDescriptor->setCertificate($certificate);

        return $keyDescriptor;
    }
}