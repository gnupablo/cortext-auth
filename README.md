cortext-auth
============

Cortext Auth is a standalone User management app and [oAuth2](http://oauth.net/2/) server , based on Symfony Silex framework (see http://silex.sensiolabs.org/documentation)

Installation
------------

**Dependencies**
Before anything, be sure to have the following packages : 

- apache 2.2+
- php 5.4+
- php5-curl module
- mySql storage : php5-mysql mysql-server mysql-client 
- [Composer](http://getcomposer.org/) : this is the fastest way to get this app up and running.  

First, clone the repository.
Then, run composer to install the dependencies

    $ git clone --recursive git@github.com:cortext/cortext-auth.git
    $ cd cortext-auth/server
    $ curl -s http://getcomposer.org/installer | php
    $ COMPOSER_PROCESS_TIMEOUT=4000 composer.phar update --prefer-dist

> composer.phar will be in your local directory.  You can also install this to your bin dir so you do not need to download it each time

**Database installation**

You need a database to store authentication informations. If you already have one with credentials, you can skip this part. Otherwise you need to create one. Use the mysql client is a way to do this. You should modify database name and account as you wish.

    $ mysql -uroot -p
    $ CREATE DATABASE IF NOT EXISTS ct_oauth;
    $ USE ct_oauth;
    $ GRANT ALL PRIVILEGES ON ct_oauth.* TO 'login'@'localhost' IDENTIFIED BY 'password' WITH GRANT OPTION;
    $ quit;

For your information, cortext-auth could also use sqlite.

**Configuration**

###Configuration Apache###

To make the site accessible, you need to configure Apache by adding a vhost in `/etc/apache2/sites-available/` that redirects to the `/server/web` directory of your `cortext-auth` installation.

As the different part of cortext are on different local domain name, you need to allow multiple cross domain.

Minimal configuration for the vhost :
    $ <VirtualHost *:80>
    $     ServerName auth.local
    $     DocumentRoot /srv/cortext-auth/server/web
    $     <Directory "/srv/cortext-auth/server/web">
    $         AllowOverride All
    $         Require all granted
    $         Header set Access-Control-Allow-Origin "*"
    $     </Directory>
    $ </VirtualHost>

Don't forget to activate the new virtual host with `a2ensite`.

More explanation on [Apache virtual host](http://httpd.apache.org/docs/current/vhosts/examples.html).

###Configuration Cortext###

By default sqlite engine is used, and the database is stored in `/server/data/`.
To change to a MySql engine, you need to replace the `db_options` statements in your parameters.json file : 
     
     "db_options": {
        "driver"    : "pdo_mysql",
        "dbname" : "ct_oauth",
        "host" : "localhost",
        "username" : "user",
        "password" : "pass"
      },

Replace of course your host/user/pass with your settings.

**Test Installation**

If you want to know if the installation is successful, you need to 

Under Linux, edit the `/etc/hosts` file with `root` rights.
Under Windows, edit the `c:\windows\system32\drivers\etc\hosts` file with a text editor like notepad.
Add the following line in the file :
    127.0.0.1 auth.local
By replacing 127.0.0.1 by the IP of your server (keep 127.0.0.1 for local installation) and replacing auth.local by the name of the auth virtual host as defined in the virtual host at previous step.

In a browser, open the address `http://auth.local` or the domain name you have used.

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
