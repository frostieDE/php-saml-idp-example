<?php

namespace App\ServiceProvider;

use App\Controller\AuthController;
use App\Controller\DashboardController;
use App\Controller\ServiceProviderController;
use App\Controller\SsoController;
use App\Controller\UserController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ControllerServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        $app['controller.auth'] = function() {
            return new AuthController();
        };

        $app['controller.dashboard'] = function() {
            return new DashboardController();
        };

        $app['controller.user'] = function() {
            return new UserController();
        };

        $app['controller.service_provider'] = function() {
            return new ServiceProviderController();
        };

        $app['controller.sso'] = function() {
            return new SsoController();
        };
    }
}