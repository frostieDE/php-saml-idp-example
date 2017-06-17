<?php

namespace App;

use Silex\Application\FormTrait;
use Silex\Application\SecurityTrait;
use Silex\Application\TwigTrait;
use Silex\Application\UrlGeneratorTrait;

class Application extends \Silex\Application {
    use TwigTrait;
    use UrlGeneratorTrait;
    use SecurityTrait;
    use FormTrait;

    public function redirectTo($route, array $parameters = [ ]) {
        return $this->redirect(
            $this->url($route, $parameters)
        );
    }
}