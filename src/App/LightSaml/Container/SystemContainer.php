<?php

namespace App\LightSaml\Container;

use LightSaml\Build\Container\SystemContainerInterface;
use Pimple\Container;

/**
 * Implement custom SystemContainer to match Silex
 */
class SystemContainer implements SystemContainerInterface {

    private $app;

    public function __construct(Container $app) {
        $this->app = $app;
    }

    public function getRequest() {
        return $this->app['request_stack']->getMasterRequest();
    }

    public function getSession() {
        return $this->app['session'];
    }

    public function getTimeProvider() {
        return $this->app[\LightSaml\Bridge\Pimple\Container\SystemContainer::TIME_PROVIDER];
    }

    public function getEventDispatcher() {
        return $this->app['dispatcher'];
    }

    public function getLogger() {
        return $this->app['monolog'];
    }
}