<?php

namespace App\LightSaml\Container;

use LightSaml\Build\Container\BuildContainerInterface;
use Pimple\Container;

class BuildContainer implements BuildContainerInterface {

    private $app;

    public function __construct(Container $app) {
        $this->app = $app;
    }

    public function getSystemContainer() {
        return $this->app['lightsaml.container.system'];
    }

    public function getPartyContainer() {
        return $this->app['lightsaml.container.party'];
    }

    public function getStoreContainer() {
        return $this->app['lightsaml.container.store'];
    }

    public function getProviderContainer() {
        return $this->app['lightsaml.container.provider'];
    }

    public function getCredentialContainer() {
        return $this->app['lightsaml.container.credential'];
    }

    public function getServiceContainer() {
        return $this->app['lightsaml.container.service'];
    }

    public function getOwnContainer() {
        return $this->app['lightsaml.container.own'];
    }
}