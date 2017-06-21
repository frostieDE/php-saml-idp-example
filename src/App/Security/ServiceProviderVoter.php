<?php

namespace App\Security;

use App\Entity\ServiceProvider;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter which checks whether an user is allowed to access a SP
 */
class ServiceProviderVoter extends Voter {

    const ACCESS = 'access';

    protected function supports($attribute, $subject) {
        return $attribute === static::ACCESS
            && $subject instanceof ServiceProvider;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token) {
        $user = $token->getUser();

        if($user === null || !$user instanceof User) {
            return false;
        }

        /** @var ServiceProvider[] $serviceProviders */
        $serviceProviders = $user->getAllowedServiceProviders();

        foreach($serviceProviders as $serviceProvider) {
            if($serviceProvider->getId() === $subject->getId()) {
                return true;
            }
        }

        return false;
    }
}