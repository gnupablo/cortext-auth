cortext-auth
============

Cortext Auth is a standalone User management app and [oAuth2](http://oauth.net/2/) server , based on Symfony Silex framework (see http://silex.sensiolabs.org/documentation)

Installation
------------

**Dependencies**
Before anything, be sure to have the following packages : 

- php 5.4 (or 5.3.2 if you don't need the standalone web server)
- php5-curl module
- default sqlite sorage : php5-sqlite 
- mySql storage : php5-mysql mysql-server mysql-client 
- [Composer](http://getcomposer.org/) : this is the fastest way to get this app up and running.  

First, clone the repository.
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

(You can also configure a classic apache/nginx virtualHost, with a local domain root redirected to the /server/web directory.)

> and you're done !

Database configuration
----------------------
By default sqlite engine is used, and the database is stored in `/server/data/`. To change to a MySql engine, you need to replace the `db_options` statements in your parameters.json file : 
     
     "db_options": {
        "driver"    : "pdo_mysql",
        "dbname" : "ct_oauth",
        "host" : "localhost",
        "username" : "user",
        "password" : "pass"
      },

Replace of course your host/user/pass with your settings.

The User management app
-----------------------
> (still in dev)

  * `/login`
  * `/register`
  * `/lostPassword`

The OAuth2 Server
-----------------

The ctProfile APIs implement the following OAuth2-compatible endpoints:

   * `/authorize` - endpoint which grants the Demo App an `authorization code`
   * `/grant`     - endpoint which grants the Demo App an `access_token` when supplied with the authorization code above
   * `/access`    - endpoint which grants the Demo App access to your protected resources (in this case, your friends) when supplied the access token above

These are the three main functions of an OAuth2 server, to authorize the user, grant the user tokens, and validate the token on
request to the APIs.  When you write your OAuth2-compatible servers, you will use very similar methods

> Note: the above urls are prefixed with `/auth` to namespace the application.
