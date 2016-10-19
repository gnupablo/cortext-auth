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

// Integration des CGU détaillées
$app->get('/conditions-generales-utilisation', function () use ($app) {
    $imageUrl=null;
    if ( $app['user'] )
      $imageUrl=$app['user.controller']->getGravatarUrl($app['user']->getEmail());
    return $app['twig']->render('conditions.twig', array('layout_template' => '@user/layout.twig', 'imageUrl' => $imageUrl));
})->bind('conditions');

// Integration des Crédits
$app->get('/credits', function () use ($app) {
    $imageUrl=null;
    if ( $app['user'] )
      $imageUrl=$app['user.controller']->getGravatarUrl($app['user']->getEmail());
    return $app['twig']->render('credits.twig', array('layout_template' => '@user/layout.twig', 'imageUrl' => $imageUrl));
})->bind('credits');

// Integration des Mentions légales
$app->get('/mentions-legales', function () use ($app) {
    $imageUrl=null;
    if ( $app['user'] )
      $imageUrl=$app['user.controller']->getGravatarUrl($app['user']->getEmail());
    return $app['twig']->render('mentions.twig', array('layout_template' => '@user/layout.twig', 'imageUrl' => $imageUrl));
})->bind('mentions');

//let's rock !
$app->run($request);
