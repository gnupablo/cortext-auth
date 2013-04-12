cortext-auth
============

Cortext Auth is a standalone User management app and [oAuth2](http://oauth.net/2/) server , based on Symfony Silex framework (see http://silex.sensiolabs.org/documentation), and php 5.4

Installation
------------

**Dependencies**
Before anything, be sure to have the following packages : 

- php 5.4
- php5-curl module
- php5-sqlite for the default storage configuration, or any storage management package you need

[Composer](http://getcomposer.org/) is the fastest way to get this app up and running.  First, clone the repository.
Then, run composer to install the dependencies

    $ git clone git://github.com/cortext/cortext-auth.git
    $ cd cortext-auth
    $ curl -s http://getcomposer.org/installer | php
    $ composer.phar install

> composer.phar will be in your local directory.  You can also install this to your bin dir so you do not need to download it each time

**Host File**

Silex requires you to [configure your web server](http://silex.sensiolabs.org/doc/web_servers.html) to run it.
The fastest way is the standalone php server : 

    $ cd server
    $ php -S localhost:3000 -t web web/index.php

> and you're done !

**If you need to re-install the app from scratch, don't forget to delete the data/ sqlite database first !**

The User management app
-----------------------
> (still in dev)

  * `/login`
  * `/register`
  * `/lostPassword`

The OAuth2 Server
-----------------

The Lock'd In APIs implement the following OAuth2-compatible endpoints:

   * `/authorize` - endpoint which grants the Demo App an `authorization code`
   * `/grant`     - endpoint which grants the Demo App an `access_token` when supplied with the authorization code above
   * `/access`    - endpoint which grants the Demo App access to your protected resources (in this case, your friends) when supplied the access token above

These are the three main functions of an OAuth2 server, to authorize the user, grant the user tokens, and validate the token on
request to the APIs.  When you write your OAuth2-compatible servers, you will use very similar methods

> Note: the above urls are prefixed with `/auth` to namespace the application.
