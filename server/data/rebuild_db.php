<?php

// rebuild the DB
$params = array(  
   'dbname' => 'ct_oauth',
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'sfx4c02m',
);     
$params['dsn'] = 'mysql:host='.$params['host'];
//$db = new PDO(sprintf('sqlite://%s', $dir));
$db = new PDO(
                $params['dsn'],
                $params['username'],
                $params['password']
                );
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE DATABASE IF NOT EXISTS `'.$params['dbname'].'`');
$db->exec('USE `'.$params['dbname'].'`');
$db->exec('DROP TABLE IF EXISTS oauth_clients');
$db->exec('DROP TABLE IF EXISTS oauth_access_tokens ');
$db->exec('DROP TABLE IF EXISTS oauth_authorization_codes');
$db->exec('DROP TABLE IF EXISTS oauth_refresh_tokens');

$db->exec('CREATE TABLE oauth_clients (client_id TEXT, client_secret TEXT, redirect_uri TEXT)');
$db->exec('CREATE TABLE oauth_access_tokens (access_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');
$db->exec('CREATE TABLE oauth_authorization_codes (authorization_code TEXT, client_id TEXT, user_id TEXT, redirect_uri TEXT, expires TIMESTAMP, scope TEXT)');
$db->exec('CREATE TABLE oauth_refresh_tokens (refresh_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');
$db->exec("CREATE TABLE `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(100) NOT NULL DEFAULT '',
  `password` VARCHAR(255) NOT NULL DEFAULT '',
  `salt` VARCHAR(255) NOT NULL DEFAULT '',
  `roles` VARCHAR(255) NOT NULL DEFAULT '',
  `name` VARCHAR(100) NOT NULL DEFAULT '',
  `time_created` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
// add test data
$db->exec('INSERT INTO oauth_clients (client_id, client_secret) VALUES ("demoapp", "demopass")');
echo "\nDatabase ".$params['dbname']." successfully created.\n";
//chmod($dir, 0777);
// $db->exec('INSERT INTO oauth_access_tokens (access_token, client_id) VALUES ("testtoken", "Some Client")');
// $db->exec('INSERT INTO oauth_authorization_codes (authorization_code, client_id) VALUES ("testcode", "Some Client")');
