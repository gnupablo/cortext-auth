<?php

//handle standalone server config
$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename))
{
    return false;
}

//bootstraping the app
$app = require_once dirname(__DIR__).'/app/bootstrap.php';

// create an http foundation request implementing OAuth2_RequestInterface
$request = OAuth2\HttpFoundationBridge\Request::createFromGlobals();

// Integration des Terms of use
$app->get('/terms', function () use ($app) {
    return $app['twig']->render('terms.twig', array('layout_template' => '@user/layout.twig'));
})->bind('terms');

//let's rock !
$app->run($request);
