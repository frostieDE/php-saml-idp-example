<?php

namespace App\Controller;

use App\Application;
use App\LightSaml\Container\BuildContainer;
use Symfony\Component\HttpFoundation\Request;

class SsoController {
    public function saml(Request $request, Application $app) {
        /** @var BuildContainer $buildContext */
        $buildContext = $app['lightsaml.container.build'];
        $receiveBuilder = new \LightSaml\Idp\Builder\Profile\WebBrowserSso\Idp\SsoIdpReceiveAuthnRequestProfileBuilder($buildContext);

        $context = $receiveBuilder->buildContext();
        $action = $receiveBuilder->buildAction();

        $action->execute($context);

        $partyContext = $context->getPartyEntityContext();
        $endpoint = $context->getEndpoint();
        $message = $context->getInboundMessage();

        $sendBuilder = new \LightSaml\Idp\Builder\Profile\WebBrowserSso\Idp\SsoIdpSendResponseProfileBuilder(
            $buildContext,
            array(new \LightSaml\Idp\Builder\Action\Profile\SingleSignOn\Idp\SsoIdpAssertionActionBuilder($buildContext)),
            $partyContext->getEntityDescriptor()->getEntityID()
        );
        $sendBuilder->setPartyEntityDescriptor($partyContext->getEntityDescriptor());
        $sendBuilder->setPartyTrustOptions($partyContext->getTrustOptions());
        $sendBuilder->setEndpoint($endpoint);
        $sendBuilder->setMessage($message);

        $context = $sendBuilder->buildContext();
        $action = $sendBuilder->buildAction();

        $action->execute($context);

        return $context->getHttpResponseContext()->getResponse();
    }
}