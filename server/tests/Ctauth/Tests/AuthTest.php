<?php

namespace Ctauth\Tests;

use Silex\WebTestCase;

class AuthTest extends WebTestCase
{

    public function createApplication()
    {
        $app = require __DIR__ . '/../../../app/bootstrap.php';
        $app['debug'] = true;
        $app['session.test'] = true;
        $app['exception_handler']->disable();
        return $app; 
    }

    public function testHomePage()
    {
       $client = $this->createClient();
       $client->followRedirects();
       $crawler = $client->request('GET', 'http://oauth.dev/');
       $form = $crawler->selectButton('Sign in')->form(array(
                '_username' => 'breucker@ifris.org',
                '_password'  => 'test',
               ));
       $client->submit($form);
       //die(print_r($client->getResponse()->getContent()));
       $this->assertTrue($client->getResponse()->isOk());
       $client->click($crawler->filter('a#btsubmit')->eq(1)->link());
       
           die(var_dump($client->getResponse ()->getContent ()));
        //$this->assertTrue(true);
        /*$this->assertCount(1, $crawler->filter('h1:contains("Contact us")'));
        $this->assertCount(1, $crawler->filter('form'));*/
    }

}
