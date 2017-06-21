<?php

namespace App\LightSaml\Provider\Attribute;

use App\Entity\User;
use LightSaml\ClaimTypes;
use LightSaml\Context\Profile\AssertionContext;
use LightSaml\Context\Profile\ProfileContext;
use LightSaml\Model\Assertion\Attribute;
use LightSaml\Provider\Attribute\AttributeValueProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AttributeValueProvider implements AttributeValueProviderInterface {

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    public function getValues(AssertionContext $context) {
        /** @var ProfileContext $profileContext */
        $profileContext = $context->getParent();

        if(!$profileContext instanceof ProfileContext) {
            throw new \RuntimeException(
                sprintf('Parent context must be of type "%s" ("%s" given)', ProfileContext::class, get_class($profileContext))
            );
        }

        /*
         * You should create a list of attributes here based on the submitting
         * Service Provider. Its entityID can be retried using:
         */
        $message = $profileContext->getInboundMessage();
        $entityId = $message->getIssuer()->getValue();

        $token = $this->tokenStorage->getToken();
        /** @var User $user */
        $user = $token->getUser();

        $attributes = [ ];

        $attributes[] = new Attribute(ClaimTypes::COMMON_NAME, $user->getUsername());
        $attributes[] = new Attribute(ClaimTypes::SURNAME, $user->getLastname());
        $attributes[] = new Attribute(ClaimTypes::GIVEN_NAME, $user->getFirstname());
        $attributes[] = new Attribute(ClaimTypes::EMAIL_ADDRESS, $user->getEmail());

        return $attributes;
    }
}