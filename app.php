<?php

$loader = include_once __DIR__ . '/vendor/autoload.php';

use App\Application;
use App\Security\UserProvider;
use App\ServiceProvider\ControllerServiceProvider;
use App\ServiceProvider\IdentityProviderServiceProvider;
use App\ServiceProvider\RouteServiceProvider;
use App\ServiceProvider\MenuServiceProvider as AppMenuServiceProvider;
use BoxedCode\Silex\Knp\MenuServiceProvider;
use Composer\Autoload\ClassLoader;
use Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use FrostieDE\Silex\EnvironmentServiceProvider;
use FrostieDE\Silex\VersionServiceProvider;
use Igorw\Silex\ConfigServiceProvider;
use Knp\Provider\ConsoleServiceProvider;
use Monolog\Logger;
use Saxulum\DoctrineOrmManagerRegistry\Provider\DoctrineOrmManagerRegistryProvider;
use Silex\Provider\CsrfServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\Validator\Mapping\Cache\DoctrineCache;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;

if($loader instanceof ClassLoader) {
    // Autoload annotations (see http://stackoverflow.com/a/31918150)
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}

ErrorHandler::register();

$app = new Application();

$app['dir'] = __DIR__;

$app->register(new EnvironmentServiceProvider());

ExceptionHandler::register($app['env'] === 'dev');

$app->register(new ConfigServiceProvider(__DIR__ . '/app/config.yml', [], null, 'config'));

$envConfigFile = __DIR__ . '/app/config.' . $app['env'] . '.yml';

if(file_exists($envConfigFile)) {
    $app->register(new ConfigServiceProvider($envConfigFile, [], null, 'config'));
}

$app->register(new VersionServiceProvider(), [
    'version.file' => __DIR__ . '/VERSION'
]);

$app->register(new MonologServiceProvider(), [
    'monolog.logfile' => __DIR__ . '/var/logs/app.log',
    'monolog.level' => $app['debug'] ? Logger::DEBUG : Logger::NOTICE,
    'monolog.use_error_handler' => true
]);

$app->register(new DoctrineServiceProvider(), array(
    'db.options' => $app['config']['db']
));

$app->register(new DoctrineOrmServiceProvider(), array(
    'orm.proxies_dir' => __DIR__ . '/var/doctrine/proxies',
    'orm.em.options' => array(
        'mappings' => [
            [
                'type' => 'annotation',
                'namespace' => 'App\Entity',
                'path' => __DIR__ . '/src/App/Entity',
                'use_simple_annotation_reader' => false
            ]
        ]
    ),
));

$app->register(new SessionServiceProvider());

$app->register(new SecurityServiceProvider(), [
    'security.firewalls' => [
        'unsecured' => [
            'pattern' => '^/login$',
            'anonymous' => true
        ],
        'secured' => [
            'pattern' => '^/',
            'form' => [
                'login_path' => '/login',
                'check_path' => '/login_check'
            ],
            'logout' => [
                'logout_path' => '/logout',
                'invalidate_session' => true
            ],
            'users' => function() use($app) {
                return new UserProvider($app['orm.em']);
            }
        ],

    ],
    'security.role_hierarchy' => [
        'ROLE_ADMIN' => [ 'ROLE_USER' ]
    ]
]);

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/app/templates',
    'twig.options' => [
        'cache' => $app['env'] === 'dev' ? false :  __DIR__ . '/var/cache/twig',
        'auto_reload' => true
    ],
    'twig.form.templates' => [
        'bootstrap_3_horizontal_layout.html.twig'
    ]
]);

$app->register(new ValidatorServiceProvider(), [
    'validator.mapping.class_metadata_factory' => function() use ($app) {
        $reader = new Doctrine\Common\Annotations\AnnotationReader;
        $loader = new Symfony\Component\Validator\Mapping\Loader\AnnotationLoader($reader);
        $cache  = new DoctrineCache(new ArrayCache());
        return new LazyLoadingMetadataFactory($loader, $cache);
    }
]);
$app->register(new TranslationServiceProvider(), [
    'locale' => 'en',
    'translator.domains' => [],
]);
$app->register(new FormServiceProvider());
$app->register(new CsrfServiceProvider());

$app->register(new DoctrineOrmManagerRegistryProvider());

$app->register(new AppMenuServiceProvider());
$app->register(new MenuServiceProvider(), [
    'knp_menu.views_path' => __DIR__ . '/vendor/knplabs/knp-menu/src/Knp/Menu/Resources/views/',
    'knp_menu.default_renderer' => 'twig'
]);

$app->register(new ServiceControllerServiceProvider());
$app->register(new ControllerServiceProvider());
$app->mount('/', new RouteServiceProvider());

$app->register(new IdentityProviderServiceProvider());

/*
 * Console commands
 */
$app->register(new ConsoleServiceProvider(), [
    'console.name' => 'Example IdP CLI',
    'console.version' => $app['version'],
    'console.project_directory' => __DIR__
]);

return $app;