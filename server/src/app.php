<?php
error_reporting(E_ALL);
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app['debug'] = true;
//$app['twig']->addExtension(new Demo\Twig\JsonStringifyExtension());

/** set up dependency injection container */
$app['oauth_storage'] = function ($app) {
    if (!file_exists($sqliteDir = __DIR__.'/../data/oauth.sqlite')) {
        // generate sqlite if it does not exist
        include_once(__DIR__.'/../data/rebuild_db.php');
    }
    return new OAuth2_Storage_Pdo(array('dsn' => 'sqlite:'.$sqliteDir));
};

$app['oauth_server'] = function($app) {
    /* OAuth2\HttpFondation\Server is a wrapper for OAuth2_Server which returns HttpFoundation\Request instead of OAuth2_Request */
    $server = new OAuth2\HttpFoundationBridge\Server($app['oauth_storage']);
    $server->addGrantType(new OAuth2_GrantType_AuthorizationCode($app['oauth_storage']));
    return $server;
};

/** load the parameters configuration */
$parameterFile = __DIR__.'/../data/parameters.json';
if (!file_exists($parameterFile)) {
    // allows you to customize parameter file
    $parameterFile = $parameterFile.'.dist';
}

$app['environments'] = array();
if (!$parameters = json_decode(file_get_contents($parameterFile), true)) {
  exit('unable to parse parameters file: '.$parameterFile);
}
// we are using an array of configurations
if (!isset($parameters['client_id'])) {
  $app['environments'] = array_keys($parameters);
  $env = $app['session']->get('config_environment');
  $parameters = isset($parameters[$env]) ? $parameters[$env] : array_shift($parameters);
}

$app['parameters'] = $parameters;

/** set up routes / controllers */
// please see the Controller classes in src/Demo/Controller and src/LockdIn/Controller for more information
$app->mount('/auth', new Ctauth\ControllerProvider());
$app->mount('/demo', new Demo\ControllerProvider());

$app->get('/', function() use($app) {
    return $app['twig']->render('demo/index.twig');
})->bind('homepage');


// create an http foundation request implementing OAuth2_RequestInterface
$request = OAuth2\HttpFoundationBridge\Request::createFromGlobals();

//let's rock !
$app->run($request);