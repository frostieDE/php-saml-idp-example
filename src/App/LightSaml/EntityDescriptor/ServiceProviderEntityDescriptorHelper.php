<?php

namespace App\LightSaml\EntityDescriptor;

use Doctrine\ORM\EntityManager;

class ServiceProviderEntityDescriptorHelper {
    private $em;

    public function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }


}