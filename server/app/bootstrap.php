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

/////params //todo get it from parameters.json
$params = array(  
   'dbname' => 'ct_oauth',
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'sfx4c02m',
);     
$params['dsn'] = 'mysql:host='.$params['host'].';dbname='.$params['dbname'];

$app->register(new Provider\DoctrineServiceProvider(), array('db.options' => array(
        'driver' => 'pdo_mysql',
        'dbname' => $params['dbname'],
        'host' => $params['host'],
        'user' => $params['username'],
        'password' => $params['password'],
        )));

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
    array('^/$', 'ROLE_USER')
); 

$app['security.role_hierarchy'] =array(
    'ROLE_ADMIN' => array('ROLE_USER')
    );


// Note: As of this writing, RememberMeServiceProvider must be registered *after* SecurityServiceProvider or SecurityServiceProvider
// throws 'InvalidArgumentException' with message 'Identifier "security.remember_me.service.secured_area" is not defined.'
$app->register(new Provider\RememberMeServiceProvider());

// These services are only required if you use the optional SimpleUser controller provider for form-based authentication.
////// session
$app->register(new Provider\SessionServiceProvider());





// SimpleUser service provider.
$app->register($u = new SimpleUser\UserServiceProvider());

 $db = new PDO( $params['dsn'], $params['username'],$params['password']);
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
            $server = new OAuth2\HttpFoundationBridge\Server($app['oauth_storage']);
            $server->addGrantType(new OAuth2_GrantType_AuthorizationCode($app['oauth_storage']));
            return $server;
        };

/** load the parameters configuration */
$parameterFile = __DIR__ . '/../data/parameters.json';
if (!file_exists($parameterFile))
{
    // allows you to customize parameter file
    $parameterFile = $parameterFile . '.dist';
}

$app['environments'] = array();
if (!$parameters = json_decode(file_get_contents($parameterFile), true))
{
    exit('unable to parse parameters file: ' . $parameterFile);
}
// we are using an array of configurations
if (!isset($parameters['client_id']))
{
    $app['environments'] = array_keys($parameters);
    $env = $app['session']->get('config_environment');
    $parameters = isset($parameters[$env]) ? $parameters[$env] : array_shift($parameters);
}

$app['parameters'] = $parameters;


////// mounting points
// please see the Controller classes in src/Ctprofile/Controller and src/LockdIn/Controller for more information
$app->mount('/auth', new Ctauth\ControllerProvider());
$app->mount('/profile', new Ctprofile\ControllerProvider());
// mounting /user
$app->mount('/user', $u); //$u is defined above as an instance of SimpleUser\UserServiceProvider

$app->get('/', function() use($app)
        {
            $app['monolog']->info('Application profile started');
            return $app['twig']->render('ctprofile/index.twig');
        })->bind('homepage');

return $app;