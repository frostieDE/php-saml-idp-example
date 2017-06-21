<?php

namespace App\ServiceProvider;

use App\Command\AddAdminUserCommand;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Knp\Console\Application as ConsoleApplication;

class CommandsServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        $app->extend('console', function(ConsoleApplication $app) {
            $app->add(new AddAdminUserCommand());

            return $app;
        });
    }
}