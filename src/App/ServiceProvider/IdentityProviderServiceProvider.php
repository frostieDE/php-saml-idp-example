<?php

namespace App\ServiceProvider;

use App\LightSaml\Container\BuildContainer;
use App\LightSaml\Container\SystemContainer;
use App\LightSaml\EntityDescriptor\ServiceProviderEntityStore;
use LightSaml\Binding\BindingFactory;
use LightSaml\Bridge\Pimple\Container\CredentialContainer;
use LightSaml\Bridge\Pimple\Container\OwnContainer;
use LightSaml\Bridge\Pimple\Container\PartyContainer;
use LightSaml\Bridge\Pimple\Container\ProviderContainer;
use LightSaml\Bridge\Pimple\Container\ServiceContainer;
use LightSaml\Bridge\Pimple\Container\StoreContainer;
use LightSaml\Builder\EntityDescriptor\SimpleEntityDescriptorBuilder;
use LightSaml\Credential\KeyHelper;
use LightSaml\Credential\X509Certificate;
use LightSaml\Credential\X509Credential;
use LightSaml\Logout\Resolver\Logout\LogoutSessionResolver;
use LightSaml\Meta\TrustOptions\TrustOptions;
use LightSaml\Model\Metadata\EntityDescriptor;
use LightSaml\Model\Metadata\KeyDescriptor;
use LightSaml\Provider\TimeProvider\SystemTimeProvider;
use LightSaml\Resolver\Credential\Factory\CredentialResolverFactory;
use LightSaml\Resolver\Endpoint\BindingEndpointResolver;
use LightSaml\Resolver\Endpoint\CompositeEndpointResolver;
use LightSaml\Resolver\Endpoint\DescriptorTypeEndpointResolver;
use LightSaml\Resolver\Endpoint\IndexEndpointResolver;
use LightSaml\Resolver\Endpoint\LocationEndpointResolver;
use LightSaml\Resolver\Endpoint\ServiceTypeEndpointResolver;
use LightSaml\Resolver\Session\SessionProcessor;
use LightSaml\Resolver\Signature\OwnSignatureResolver;
use LightSaml\Store\Credential\Factory\CredentialFactory;
use LightSaml\Store\EntityDescriptor\FixedEntityDescriptorStore;
use LightSaml\Store\Id\IdArrayStore;
use LightSaml\Store\Request\RequestStateSessionStore;
use LightSaml\Store\Sso\SsoStateSessionStore;
use LightSaml\Store\TrustOptions\FixedTrustOptionsStore;
use LightSaml\Validator\Model\Assertion\AssertionTimeValidator;
use LightSaml\Validator\Model\Assertion\AssertionValidator;
use LightSaml\Validator\Model\NameId\NameIdValidator;
use LightSaml\Validator\Model\Signature\SignatureValidator;
use LightSaml\Validator\Model\Statement\StatementValidator;
use LightSaml\Validator\Model\Subject\SubjectValidator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class IdentityProviderServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {

        /*
         * System Container stuff
         */
        $app[\LightSaml\Bridge\Pimple\Container\SystemContainer::TIME_PROVIDER] = function($app) {
            return new SystemTimeProvider();
        };

        $app['lightsaml.container.system'] = function($app) {
            return new SystemContainer($app);
        };

        /*
         * Store Container stuff
         *
         * TODO: make the two IDs configurable!
         */

        $app[StoreContainer::REQUEST_STATE_STORE] = function ($app) {
            return new RequestStateSessionStore($app['session'], 'main');
        };

        $app[StoreContainer::ID_STATE_STORE] = function () {
            return new IdArrayStore(); // TODO!
        };

        $app[StoreContainer::SSO_STATE_STORE] = function ($app) {
            return new SsoStateSessionStore($app['session'], 'samlsso');
        };

        $app['lightsaml.container.store'] = function($app) {
            return new StoreContainer($app);
        };

        /*
         * Own Container stuff
         */

        $app['lightsaml.container.own_credential'] = $app->factory(function($usage) use ($app) {
            $credential = new X509Credential(
                X509Certificate::fromFile(__DIR__ . '/../../../app/certs/' . $app['config']['idp']['cert']['file']),
                KeyHelper::createPrivateKey(__DIR__ . '/../../../app/certs/' . $app['config']['idp']['cert']['key'], '', true)
            );
            $credential->setEntityId($app['config']['idp']['id']);

            return $credential;
        });

        $app[OwnContainer::OWN_CREDENTIALS] = function($app) {
            return [ $app['lightsaml.container.own_credential'] ];
        };

        $app[OwnContainer::OWN_ENTITY_DESCRIPTOR_PROVIDER] = function($app) {
            $provider = new SimpleEntityDescriptorBuilder(
                $app['config']['idp']['id'],
                null,
                $app['config']['idp']['url'],
                $app['lightsaml.container.own_credential']->getCertificate()
            );

            return $provider;
        };

        $app['lightsaml.container.own'] = function($app) {
            return new OwnContainer($app);
        };

        /*
         * Credential Container stuff
         */

        $app[CredentialContainer::CREDENTIAL_STORE] = function ($app) {
            $factory = new CredentialFactory();

            return $factory->build(
                $app['lightsaml.container.party']->getIdpEntityDescriptorStore(),
                $app['lightsaml.container.party']->getSpEntityDescriptorStore(),
                $app['lightsaml.container.own']->getOwnCredentials()
            );
        };

        $app['lightsaml.container.credential'] = function($app) {
            return new CredentialContainer($app);
        };

        /*
         * Party Container stuff
         */

        $app[PartyContainer::IDP_ENTITY_DESCRIPTOR] = function ($app) {
            $store = new FixedEntityDescriptorStore();

            $ownEntityDescriptor = $app[OwnContainer::OWN_ENTITY_DESCRIPTOR_PROVIDER]->get();
            $store->add($ownEntityDescriptor);

            return $store;
        };

        $app[PartyContainer::SP_ENTITY_DESCRIPTOR] = function ($app) {
            return new ServiceProviderEntityStore($app['orm.em']);
        };

        $app[PartyContainer::TRUST_OPTIONS_STORE] = function () {
            return new FixedTrustOptionsStore(new TrustOptions());
        };

        $app['lightsaml.container.party'] = function($app) {
            return new PartyContainer($app);
        };

        /*
         * Provider Container stuff
         */

        $app[ProviderContainer::ATTRIBUTE_VALUE_PROVIDER] = function () {
            return (new \LightSaml\Provider\Attribute\FixedAttributeValueProvider())
                ->add(new \LightSaml\Model\Assertion\Attribute(
                    \LightSaml\ClaimTypes::COMMON_NAME,
                    'common-name'
                ))
                ->add(new \LightSaml\Model\Assertion\Attribute(
                    \LightSaml\ClaimTypes::GIVEN_NAME,
                    'first'
                ))
                ->add(new \LightSaml\Model\Assertion\Attribute(
                    \LightSaml\ClaimTypes::SURNAME,
                    'last'
                ))
                ->add(new \LightSaml\Model\Assertion\Attribute(
                    \LightSaml\ClaimTypes::EMAIL_ADDRESS,
                    'somebody@example.com'
                ));

        };

        $app[ProviderContainer::SESSION_INFO_PROVIDER] = function () {
            return new \LightSaml\Provider\Session\FixedSessionInfoProvider(
                time() - 600,
                'session-index',
                \LightSaml\SamlConstants::AUTHN_CONTEXT_PASSWORD_PROTECTED_TRANSPORT
            );
        };

        $app[ProviderContainer::NAME_ID_PROVIDER] = function () use ($app) {
            $nameId = new \LightSaml\Model\Assertion\NameID('name@id.com');
            $nameId
                ->setFormat(\LightSaml\SamlConstants::NAME_ID_FORMAT_EMAIL)
                ->setNameQualifier($app['lightsaml.container.build']->getOwnContainer()->getOwnEntityDescriptorProvider()->get()->getEntityID())
            ;

            return new \LightSaml\Provider\NameID\FixedNameIdProvider($nameId);
        };

        $app['lightsaml.container.provider'] = function($app) {
            return new ProviderContainer($app);
        };

        /*
         * Service container stuff
         */

        $app[ServiceContainer::NAME_ID_VALIDATOR] = function () {
            return new NameIdValidator();
        };

        $app[ServiceContainer::ASSERTION_TIME_VALIDATOR] = function () {
            return new AssertionTimeValidator();
        };

        $app[ServiceContainer::ASSERTION_VALIDATOR] = function (Container $c) {
            $nameIdValidator = $c[ServiceContainer::NAME_ID_VALIDATOR];

            return new AssertionValidator(
                $nameIdValidator,
                new SubjectValidator($nameIdValidator),
                new StatementValidator()
            );
        };

        $app[ServiceContainer::ENDPOINT_RESOLVER] = function () {
            return new CompositeEndpointResolver(array(
                new BindingEndpointResolver(),
                new DescriptorTypeEndpointResolver(),
                new ServiceTypeEndpointResolver(),
                new IndexEndpointResolver(),
                new LocationEndpointResolver(),
            ));
        };

        $app[ServiceContainer::BINDING_FACTORY] = function ($app) {
            return new BindingFactory($app['dispatcher']);
        };

        $app[ServiceContainer::CREDENTIAL_RESOLVER] = function ($app) {
            $factory = new CredentialResolverFactory($app['lightsaml.container.credential']->getCredentialStore());

            return $factory->build();
        };

        $app[ServiceContainer::SIGNATURE_RESOLVER] = function (Container $c) {
            $credentialResolver = $c[ServiceContainer::CREDENTIAL_RESOLVER];

            return new OwnSignatureResolver($credentialResolver);
        };

        $app[ServiceContainer::SIGNATURE_VALIDATOR] = function (Container $c) {
            $credentialResolver = $c[ServiceContainer::CREDENTIAL_RESOLVER];

            return new SignatureValidator($credentialResolver);
        };

        $app[ServiceContainer::LOGOUT_SESSION_RESOLVER] = function ($app) {
            return new LogoutSessionResolver($app['lightsaml.container.store']->getSsoStateStore());
        };

        $app[ServiceContainer::SESSION_PROCESSOR] = function ($app) {
            return new SessionProcessor($app['lightsaml.container.store']->getSsoStateStore(), $app['lightsaml.container.system']->getTimeProvider());
        };

        $app['lightsaml.container.service'] = function($app) {
            return new ServiceContainer($app);
        };

        /*
         * Build Container stuff
         */

        $app['lightsaml.container.build'] = function($app) {
            return new BuildContainer($app);
        };
    }
}