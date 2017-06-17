<?php

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\HelperSet;

set_time_limit(0);

$app = require_once __DIR__ . '/../app.php';

/** @var Knp\Console\Application $cli */
$cli = $app['console'];

/*
 * Doctrine CLI
 */
$helperSet = new HelperSet(array(
    'db' => new ConnectionHelper($app['orm.em']->getConnection()),
    'em' => new EntityManagerHelper($app['orm.em'])
));

$cli->setHelperSet($helperSet);
ConsoleRunner::addCommands($cli);

$cli->run();