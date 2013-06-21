<?php

namespace Ctauth;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;

class ControllerProvider implements ControllerProviderInterface
{
    private function log($message, Application $app)
    {
         $app['monolog']->info($message."\n".' query :  '.print_r($app['request']->query, true)."\n".' request :  '.print_r($app['request']->request, true));
    }
    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];
        
        $controllers->get('/authorize', function (Application $app) {
           $this->log('reveived authorize GET request', $app);
            if (!$app['oauth_server']->validateAuthorizeRequest($app['request'])) {
                return $app['oauth_server']->getResponse();
            }
            return $app['twig']->render('ctauth/authorize.twig');
        })->bind('authorize');

        $controllers->post('/authorize', function (Application $app) {
          $this->log('reveived authorize POST request : ', $app);
            $authorized = (bool) $app['request']->request->get('authorize');
            
            return $app['oauth_server']->handleAuthorizeRequest($app['request'], $authorized);
        })->bind('authorize_post');

        $controllers->post('/grant', function(Application $app) {
            $this->log('reveived grant request : ', $app);
            
            //$app['monolog']->info('[oauthserver] grant response : '.print_r($r));
            return $app['oauth_server']->handleGrantRequest($app['request']);
        })->bind('grant');

        $controllers->get('/access', function(Application $app) {
            $this->log('reveived access request : ', $app);   
            $server = $app['oauth_server'];
            if (!$server->verifyAccessRequest($app['request'])) {
                return $server->getResponse();
            } else {
                return new Response(print_r(array('friends' => array('john', 'matt', 'jane'))));
            }
        })->bind('access');

        return $controllers;
    }
}