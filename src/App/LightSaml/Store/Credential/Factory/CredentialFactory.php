<?php

namespace App\LightSaml\Store\Credential\Factory;

use LightSaml\Error\LightSamlBuildException;
use LightSaml\Store\Credential\CompositeCredentialStore;
use LightSaml\Store\Credential\MetadataCredentialStore;
use LightSaml\Store\Credential\StaticCredentialStore;
use LightSaml\Store\EntityDescriptor\EntityDescriptorStoreInterface;

class CredentialFactory {
    public function build(
        EntityDescriptorStoreInterface $idpEntityDescriptorStore,
        EntityDescriptorStoreInterface $spEntityDescriptorStore,
        array $ownCredentials,
        array $extraCredentials = null
    ) {
        if (empty($ownCredentials)) {
            throw new LightSamlBuildException('There are no own credentials');
        }

        $store = new CompositeCredentialStore();
        $ownCredentialsStore = new StaticCredentialStore();
        foreach ($ownCredentials as $credential) {
            $ownCredentialsStore->add($credential);
        }
        $store->add($ownCredentialsStore);

        $store->add(new MetadataCredentialStore($idpEntityDescriptorStore));
        $store->add(new MetadataCredentialStore($spEntityDescriptorStore));

        $extraCredentialsStore = new StaticCredentialStore();
        $store->add($extraCredentialsStore);
        if ($extraCredentials) {
            foreach ($extraCredentials as $credential) {
                $extraCredentialsStore->add($credential);
            }
        }

        return $store;
    }
}