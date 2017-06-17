<?php

namespace App\Controller;

use App\Application;
use Symfony\Component\HttpFoundation\Request;

class AuthController {
    public function index(Request $request, Application $app) {
        return $app->render('login/index.html.twig', array(
            'error'         => $app['security.last_error']($request),
            'username'      => $app['session']->get('_security.last_username'),
        ));
    }

    public function login(Request $request, Application $app) {
        throw new \LogicException('This code should not be executed');
    }

    public function logout(Request $request, Application $app) {
        throw new \LogicException('This code should not be executed');
    }
}