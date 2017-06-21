<?php

namespace App\Saml;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Firewall\ExceptionListener as BaseExceptionListener;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class ExceptionListener extends BaseExceptionListener {

    use TargetPathTrait;

    private $providerKey;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationTrustResolverInterface $trustResolver, HttpUtils $httpUtils, $providerKey, AuthenticationEntryPointInterface $authenticationEntryPoint = null, $errorPage = null, AccessDeniedHandlerInterface $accessDeniedHandler = null, LoggerInterface $logger = null, $stateless = false) {
        parent::__construct($tokenStorage, $trustResolver, $httpUtils, $providerKey, $authenticationEntryPoint, $errorPage, $accessDeniedHandler, $logger, $stateless);

        $this->providerKey = $providerKey;
    }

    protected function setTargetPath(Request $request) {
        if ($request->hasSession() && !$request->isXmlHttpRequest()) {
            $this->saveTargetPath($request->getSession(), $this->providerKey, $request->getUri());
        }
    }
}