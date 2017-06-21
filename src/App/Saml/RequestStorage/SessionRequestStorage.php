<?php

namespace App\Saml\RequestStorage;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\RequestStack;

class SessionRequestStorage implements RequestStorageInterface {
    private $parameterName;
    private $requestStack;

    private $logger;

    const DEFAULT_PARAMETERNAME = 'SAMLRequest';

    public function __construct(RequestStack $requestStack, $parameterName = self::DEFAULT_PARAMETERNAME, LoggerInterface $logger = null) {
        $this->parameterName = $parameterName;
        $this->requestStack = $requestStack;

        $this->logger = $logger ?? new NullLogger();
    }

    public function save() {
        $request = $this->requestStack->getMasterRequest();
        $session = $request->getSession();

        if($session === null) {
            $this->logger->debug('Do not save any SAML request as no session is associated to the current request');
            return;
        }

        if($request->isMethod('POST') !== true) {
            $this->logger->debug('No POST request, thus no potential SAML request');
            return;
        }

        if($request->request->has($this->parameterName) !== true) {
            $this->logger->debug('No SAML request found');
            return;
        }

        $this->logger->debug('SAML request found');

        $samlRequest = $request->request->get($this->parameterName);
        $session->set($this->parameterName, $samlRequest);

        $this->logger->debug('SAML request stored in current session');
    }

    public function load() {
        $request = $this->requestStack->getMasterRequest();
        $session = $request->getSession();

        if($session === null) {
            $this->logger->debug('Do not save any SAML request as no session is associated to the current request');
            return;
        }

        if($request->isMethod('POST') && $request->request->has($this->parameterName)) {
            $this->logger->debug('Do not fetch SAML request from database as another SAML request is incoming');
            return;
        }

        if($session->has($this->parameterName) !== true) {
            $this->logger->debug('No SAML request found in session');
            return;
        }

        $this->logger->debug('SAML request found');

        $samlRequest = $session->get($this->parameterName);
        $request->setMethod('POST');
        $request->request->set($this->parameterName, $samlRequest);

        $this->logger->debug('SAML request stored in current request');
    }

    public function clear() {
        $request = $this->requestStack->getMasterRequest();
        $session = $request->getSession();

        if($session === null) {
            $this->logger->debug('Do not save any SAML request as no session is associated to the current request');
            return;
        }

        if($session->has($this->parameterName) === true) {
            $session->remove($this->parameterName);
            $this->logger->debug('SAML request removed from session');
            return;
        }
    }
}