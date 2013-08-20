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
     * get user profile infos
     * @param \Silex\Application $app
     * @return type
     */
    private function getUserProfile(Application $app, $userId)
    {
        return $app['user.manager']->getUserProfile($userId);        
    }

    public function connect(Application $app)
    {
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->get('/authorize', function (Application $app)
                {
                    $this->log('reveived authorize GET request', $app);
                    $client_id = $app['request']->get('client_id');
                    $user = $app['user.manager']->getCurrentUser();
                    // if(!$user) die('no user !');
                    if (!$app['oauth_server']->validateAuthorizeRequest($app['request']))
                    {
                        return $app['oauth_server']->getResponse();
                    }
                    return $app['twig']->render('ctauth/authorize.twig', array('client_id' => $client_id, 'user'=>$user));
                })->bind('authorize');

        $controllers->post('/authorize', function (Application $app)
                {
                    $userId = $app['user.manager']->getCurrentUser()->getId();
                    $this->log('reveived authorize POST request for user : '.$userId , $app);
                    $authorized = (bool) $app['request']->request->get('authorize');

                    return $app['oauth_server']->handleAuthorizeRequest($app['request'], $authorized, $userId);
                })->bind('authorize_post');

        $controllers->post('/grant', function(Application $app)
                {
                    $this->log('received grant request : code : '.$app['request']->get('code'), $app);

                    //$app['monolog']->info('[oauthserver] grant response : '.print_r($r));
                    return $app['oauth_server']->handleGrantRequest($app['request']);
                })->bind('grant');

        $controllers->get('/access', function(Application $app)
                {
                    $this->log('received access request: access token :'.$app['request']->get('access_token'), $app);
                    $server = $app['oauth_server'];
                    if (!$server->verifyAccessRequest($app['request']))
                    {
                        //die(print_r($server->getResponse()));
                        $this->log('access could not be verified, returning response ', $app);
                        return $server->getResponse();
                    } else
                    {

                        //die(print_r($server->getResponse()));
                        $tokenDatas = $server->getAccessTokenData($app['request']);
                        $this->log('token datas retrieved :'.json_encode($tokenDatas), $app);
                        $this->log("access ok, getting user profile: ".$this->getUserProfile($app, $tokenDatas['user_id']), $app);
                        return new Response(json_encode(array('profile'=>$this->getUserProfile($app, $tokenDatas['user_id']))));
                    }
                })->bind('access');

        return $controllers;
    }

}