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
        $app['monolog']->info($message);
    }

    /**
     * get current user profile infos
     * @param \Silex\Application $app
     * @return type
     */
    private function getUserInfo(Application $app)
    {
        $user = $app['user.manager']->getUser();        
        return json_encode($user->getuserInfos());
    }

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/authorize', function (Application $app)
                {
                    $this->log('reveived authorize GET request', $app);
                    $client_id = $app['request']->get('client_id');
                    if (!$app['oauth_server']->validateAuthorizeRequest($app['request']))
                    {
                        return $app['oauth_server']->getResponse();
                    }
                    return $app['twig']->render('ctauth/authorize.twig', array('client_id' => $client_id));
                })->bind('authorize');

        $controllers->post('/authorize', function (Application $app)
                {
                    $this->log('reveived authorize POST request : ', $app);
                    $authorized = (bool) $app['request']->request->get('authorize');

                    return $app['oauth_server']->handleAuthorizeRequest($app['request'], $authorized);
                })->bind('authorize_post');

        $controllers->post('/grant', function(Application $app)
                {
                    $this->log('reveived grant request : ', $app);

                    //$app['monolog']->info('[oauthserver] grant response : '.print_r($r));
                    return $app['oauth_server']->handleGrantRequest($app['request']);
                })->bind('grant');

        $controllers->get('/access', function(Application $app)
                {
                    $this->log('received access request: ', $app);
                    $server = $app['oauth_server'];
                    $this->log('verifying access request: '.$server->verifyAccessRequest($app['request']), $app);
                    $this->log('user profile : '.json_encode($this->getUserInfo($app)));  
                    if (!$server->verifyAccessRequest($app['request']))
                    {
                        //die(print_r($server->getResponse()));
                        $this->log('access could not be verified, returning response ', $app);
                        return $server->getResponse();
                    } else
                    {
                        //die(print_r($server->getResponse()));
                        $this->log("access ok, getting user profile: ".$this->getUserInfo($app));
                        return new Response(array('profile'=>$this->getUserInfo($app)));
                    }
                })->bind('access');

        return $controllers;
    }

}