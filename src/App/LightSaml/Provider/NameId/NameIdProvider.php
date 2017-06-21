<?php

namespace App\LightSaml\Provider\NameId;

use App\Entity\User;
use LightSaml\Build\Container\OwnContainerInterface;
use LightSaml\Context\Profile\AbstractProfileContext;
use LightSaml\Model\Assertion\NameID;
use LightSaml\Provider\NameID\NameIdProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NameIdProvider implements NameIdProviderInterface {

    private $tokenStorage;
    private $ownContainer;

    public function __construct(OwnContainerInterface $ownContainer, TokenStorageInterface $tokenStorage) {
        $this->ownContainer = $ownContainer;
        $this->tokenStorage = $tokenStorage;
    }

    public function getNameID(AbstractProfileContext $context) {
        $token = $this->tokenStorage->getToken();
        /** @var User $user */
        $user = $token->getUser();

        $nameId = new NameID($user->getUsername());
        $nameId
            ->setFormat(\LightSaml\SamlConstants::NAME_ID_FORMAT_PERSISTENT)
            ->setNameQualifier($this->ownContainer->getOwnEntityDescriptorProvider()->get()->getEntityID());

        return $nameId;
    }
}