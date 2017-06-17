<?php

namespace App\Controller;

use App\Application;
use Symfony\Component\HttpFoundation\Request;

class DashboardController {
    public function index(Request $request, Application $app) {
        return $app->render('dashboard/index.html.twig');
    }
}