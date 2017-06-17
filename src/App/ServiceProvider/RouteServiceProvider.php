<?php

namespace App\ServiceProvider;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;

class RouteServiceProvider implements ControllerProviderInterface {

    public function connect(Application $app) {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];

        /*
         * Dashboard
         */
        $controllers->get('/dashboard', 'controller.dashboard:index')
            ->bind('dashboard');

        /*
         * Security
         */
        $controllers->match('/login', 'controller.auth:index')
            ->method('GET|POST')
            ->bind('login');

        /*
         * Users
         */
        $controllers->get('/users', 'controller.user:index')
            ->bind('users');

        $controllers->match('/users/add', 'controller.user:add')
            ->method('GET|POST')
            ->bind('add_user');

        $controllers->match('/users/edit/{id}', 'controller.user:edit')
            ->method('GET|POST')
            ->bind('edit_user');

        /*
         * Service Providers
         */
        $controllers->get('/sp', 'controller.service_provider:index')
            ->bind('service_providers');

        $controllers->match('/sp/add', 'controller.service_provider:add')
            ->method('GET|POST')
            ->bind('add_service_provider');

        $controllers->match('/sp/edit/{id}', 'controller.service_provider:edit')
            ->method('GET|POST')
            ->bind('edit_service_provider');

        $controllers->get('/sp/{id}/certificate', 'controller.service_provider:certificate')
            ->bind('service_provider_cert');

        /*
         * SSO
         */
        $controllers->match('/idp/saml', 'controller.sso:saml')
            ->method('GET|POST');

        /*
         * Profile
         */
        $controllers->match('/profile', 'controller.profile:index')
            ->bind('profile');

        return $controllers;
    }
}