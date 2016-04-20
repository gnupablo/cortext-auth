<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Provider;

$app = new Silex\Application();
///debug mode
$app['debug'] = true;
///// service Provider
$app->register(new Provider\ServiceControllerServiceProvider());

///////// logging ////////////
$app->register(new Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../log/ctauth.log',
    'monolog.level' => Monolog\Logger::DEBUG,
));

////// session
$app->register(new Provider\SessionServiceProvider());

///// parameters
require_once  __DIR__ .'/config.php';
//die(var_dump( $app['parameters']));

//// url generator
$app->register(new Provider\UrlGeneratorServiceProvider());
/// twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views',
));
//twig extension
$app['twig']->addExtension(new Ctprofile\Twig\JsonStringifyExtension());


//////// Security providers /////////
//////// see https://github.com/jasongrimes/silex-simpleuser ///

$dbOptions = $app['parameters']['db_options'];
//die(var_dump($dbOptions));

$app->register(new Provider\DoctrineServiceProvider(), array('db.options' => $dbOptions));

$app->register(new Provider\SecurityServiceProvider());
$app['security.firewalls'] = array(
    'secured' => array(
        'pattern' => '^/',
        'anonymous' => array(),
        'remember_me' => array(),
        'form' => array(
            'login_path' => '/user/login',
            'check_path' => '/user/login_check',
        ),
        'logout' => array(
            'logout_path' => '/user/logout',
        ),
        'users' => $app->share(function($app)
                {
                    return $app['user.manager'];
                }),
    )
);

$app['security.access_rules'] = array(
    array('^/$', 'ROLE_USER'),
    array('^/auth/authorize.*', 'ROLE_USER')
); 

$app['security.role_hierarchy'] =array(
    'ROLE_ADMIN' => array('ROLE_USER')
    );

//fix twig  'is_granted' bug @todo : do it with config, not with a hard fix...
$function = new Twig_SimpleFunction('is_granted', function($role) use ($app){
    return $app['security']->isGranted($role);
});
$app['twig']->addFunction($function);

// Note: As of this writing, RememberMeServiceProvider must be registered *after* SecurityServiceProvider or SecurityServiceProvider
// throws 'InvalidArgumentException' with message 'Identifier "security.remember_me.service.secured_area" is not defined.'
$app->register(new Provider\RememberMeServiceProvider());

//Mail service (use to recover password)
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app['swiftmailer.options'] = $app['parameters']['mailer'];


// SimpleUser service provider.
$app->register($u = new SimpleUser\UserServiceProvider());

 $db = new PDO( $app['parameters']['db_options']['dsn'],  $app['parameters']['db_options']['user'], $app['parameters']['db_options']['password']);
 $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 $app['db_oauth'] = $db;
/** set up dependency injection container */
$app['oauth_storage'] = function ($app)
        { 
            return new OAuth2_Storage_Pdo($app['db_oauth']);
        };

$app['oauth_server'] = function($app)
        {
            /* OAuth2\HttpFondation\Server is a wrapper for OAuth2_Server which returns HttpFoundation\Request instead of OAuth2_Request */
            $server = new OAuth2\HttpFoundationBridge\Server($app['oauth_storage'], array('access_lifetime'=> $app['parameters']['oauth']['access_lifetime']));
            $server->addGrantType(new OAuth2_GrantType_AuthorizationCode($app['oauth_storage']));
            $server->addGrantType(new OAuth2_GrantType_RefreshToken($app['oauth_storage']));
            return $server;
        };

////// mounting points
// please see the Controller classes in src/Ctprofile/Controller and src/Ctprofile/Controller for more information
$app['monolog']->info('Application parameters :'.print_r($app['parameters'], true));
$app->mount('/auth', new Ctauth\ControllerProvider());
$app->mount('/profile', new Ctprofile\ControllerProvider());
// mounting /user
$app->mount('/user', $u); //$u is defined above as an instance of SimpleUser\UserServiceProvider

$app->get('/test', function() use($app)
        {
            $app['monolog']->info('Application profile started');
            return $app['twig']->render('ctprofile/index.twig');
        })->bind('test');

$app->get('/', function() use($app)
        {
            return $app->redirect('/user');
        })->bind('homepage');
        
return $app;
