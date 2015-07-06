<?php

// load params
require_once dirname(__FILE__).'/../app/config.php';

// rebuild the DB

$params = $app['parameters']['db_options'];

if($params['driver']=='pdo_mysql'){
  //mysql
  try {
    $db = new PDO(
                  $params['dsn'],
                  $params['user'],
                  $params['password']
                  );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //create database if not exists
    $db->exec('CREATE DATABASE IF NOT EXISTS `'.$params['dbname'].'`');
    $db->exec('USE `'.$params['dbname'].'`');

    //drop tables if exists
    $db->exec('DROP TABLE IF EXISTS oauth_clients');
    $db->exec('DROP TABLE IF EXISTS oauth_access_tokens ');
    $db->exec('DROP TABLE IF EXISTS oauth_authorization_codes');
    $db->exec('DROP TABLE IF EXISTS oauth_refresh_tokens');
    $db->exec('DROP TABLE IF EXISTS users');
    $db->exec('DROP TABLE IF EXISTS users_infos');

    //create tables (for oAuth server and user management)
    $db->exec('CREATE TABLE oauth_clients (client_id TEXT, client_secret TEXT, redirect_uri TEXT)');
    $db->exec('CREATE TABLE oauth_access_tokens (access_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');
    $db->exec('CREATE TABLE oauth_authorization_codes (authorization_code TEXT, client_id TEXT, user_id TEXT, redirect_uri TEXT, expires TIMESTAMP, scope TEXT)');
    $db->exec('CREATE TABLE oauth_refresh_tokens (refresh_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');
    $db->exec("CREATE TABLE `users` (`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,`email` VARCHAR(100) NOT NULL DEFAULT '',`password` VARCHAR(255) NOT NULL DEFAULT '',`salt` VARCHAR(255) NOT NULL DEFAULT '',`roles` VARCHAR(255) NOT NULL DEFAULT '',`name` VARCHAR(100) NOT NULL DEFAULT '',`time_created` INT NOT NULL DEFAULT 0,PRIMARY KEY (`id`),UNIQUE KEY `unique_email` (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    $db->exec("CREATE TABLE `users_infos` ( `user_id` int(11) UNSIGNED NOT NULL, `description` TEXT DEFAULT NULL, `website` VARCHAR(255) DEFAULT NULL, `birthdate` date DEFAULT NULL, `last_connexion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, `city` VARCHAR(250) DEFAULT NULL, `country` VARCHAR(250) DEFAULT NULL, `institution` VARCHAR(500) DEFAULT NULL, `activity_domain` VARCHAR(500) DEFAULT NULL, `research_domain` VARCHAR(500) DEFAULT NULL, PRIMARY KEY (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

  }
  catch (PDOException $e){
    echo "PDO creation Error: ".$e->getMessage(); 
  }
}

// add fake test client app
try {
    $db->exec("INSERT INTO `oauth_clients` (client_id, client_secret) VALUES ('demoapp', 'demopass')");
    $db->exec("INSERT INTO `oauth_clients` (`client_id` ,`client_secret`) VALUES ('cortext-dashboard',  'c0rt3xt')");
    echo "\nDatabase ".$params['dbname']." successfully created.\n";
}catch (PDOException $e){
        echo "PDO Error: ".$e->getMessage(); 
}

