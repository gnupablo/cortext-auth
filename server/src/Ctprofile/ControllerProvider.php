<?php

namespace Ctprofile;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Response;

class ControllerProvider implements ControllerProviderInterface
{
    
    public function connect(Application $app)
    {
        
        
        // creates a new controller based on the default route
        $controllers = $app['controllers_factory'];

        $controllers->post('/authorized', function(Application $app) {
            $app['session']->set('config_environment', $app['request']->get('environment'));
            return $app->redirect($app['url_generator']->generate('homepage'));
        })->bind('set_environment');

        $controllers->get('/authorized', function(Application $app) {
            $server = $app['oauth_server'];
            $app['monolog']->info("[ctprofile] GET /authorized");

            // the user denied the authorization request
            if (!$code = $app['request']->get('code')) {
                return $app['twig']->render('ctprofile/denied.twig');
            }

            // exchange authorization code for access token
            $query = array(
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'client_id'     => $app['parameters']['client_id'],
                'client_secret' => $app['parameters']['client_secret'],
                'redirect_uri'  => $app['url_generator']->generate('authorize_redirect', array(), true),
            );
            $app['monolog']->info('sending auth code to server for access token : '.print_r($query, true));

            // call the API using curl
            $curl = new Curl();
            $grantRoute = $app['parameters']['token_route'];
            $endpoint = 0 === strpos($grantRoute, 'http') ? $grantRoute : $app['url_generator']->generate($grantRoute, array(), true);
            
            $response = $curl->request($endpoint, $query, 'POST', $app['parameters']['curl_options']);
            //$response = $this->http_post($endpoint, $query);
            $app['monolog']->info( $endpoint .' response : '.print_r($response, true));
            $json = json_decode($response['response'], true);
            //die(print_r($response));
            // render error if applicable
            $error = array();
            if ($response['errorNumber']) {
                // cURL error
                $error['error_description'] = $response['errorMessage'];
            } else {
                // OAuth error
                $error = $json;
            }

            // if it is succesful, call the API with the retrieved token
            if (isset($json['access_token'])) {
                $token = $json['access_token'];
                // make request to the API for awesome data
                $params = array_merge(array('access_token' => $token), $app['parameters']['resource_params']);
                $apiRoute = $app['parameters']['resource_route'];
                $endpoint = 0 === strpos($apiRoute, 'http') ? $apiRoute : $app['url_generator']->generate($apiRoute, array(), true);
                $response = $curl->request($endpoint, $params, $app['parameters']['resource_method'], $app['parameters']['curl_options']);
                //die(print_r($response['response']));
                $json = json_decode($response['response'], true);
                return $app['twig']->render('ctprofile/granted.twig', array('response' => $json ? $json : $response, 'token' => $token, 'endpoint' => $endpoint));
            }

            return $app['twig']->render('ctprofile/error.twig', array('response' => $error));
        })->bind('authorize_redirect');

        return $controllers;
    }
    
    
    /**
     * make an http POST request and return the response content and headers
     * @param string $url    url of the requested script
     * @param array $data    hash array of request variables
     * @return returns a hash array with response content and headers in the following form:
     *  array ('content'=>'<html></html>'
     *        , 'headers'=>array ('HTTP/1.1 200 OK', 'Connection: close', ...)
     *       )
     */
    function http_post($url, $data)
    {
        $data_url = http_build_query($data);
        $data_len = strlen($data_url);

        return array('content' => file_get_contents($url, false, stream_context_create(array('http' => array('method' => 'POST'
                            , 'header' => "Connection: close\r\nContent-Length: $data_len\r\n"
                            , 'content' => $data_url
                            ))))
            , 'headers' => $http_response_header
        );
    }
}
