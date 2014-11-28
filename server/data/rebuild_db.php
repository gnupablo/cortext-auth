<?php

// load params
require_once dirname(__FILE__).'/../app/config.php';
// rebuild the DB


$params = $app['parameters']['db_options'];
//$db = new PDO(sprintf('sqlite://%s', $dir));

if($params['driver']=='pdo_sqlite'){
  //sqlite
    // remove sqlite file if it exists
    if (file_exists($dbfile)) {
        unlink($dbfile);
        touch($dbfile);
        chmod($dbfile, 0777);
    }
    
    if (!is_writable($dbDir)) {
        // try to set permissions.

        if (!@chmod($dbDir, 0777)) {
            throw new Exception("Unable to write to $dbfile");
        }
    }

    try{
      $db = new PDO($params['dsn']);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          //drop tables if exists
      $db->exec('DROP TABLE IF EXISTS oauth_clients');
      $db->exec('DROP TABLE IF EXISTS oauth_access_tokens ');
      $db->exec('DROP TABLE IF EXISTS oauth_authorization_codes');
      $db->exec('DROP TABLE IF EXISTS oauth_refresh_tokens');
      $db->exec('DROP TABLE IF EXISTS users');
      $db->exec("
                PRAGMA synchronous = OFF;
                PRAGMA journal_mode = MEMORY;
                BEGIN TRANSACTION;
                CREATE TABLE \"oauth_access_tokens\" (
                  \"access_token\" text,
                  \"client_id\" text,
                  \"user_id\" text,
                  \"expires\" timestamp NOT NULL ,
                  \"scope\" text
                );
                CREATE TABLE \"oauth_authorization_codes\" (
                  \"authorization_code\" text,
                  \"client_id\" text,
                  \"user_id\" text,
                  \"redirect_uri\" text,
                  \"expires\" timestamp NOT NULL ,
                  \"scope\" text
                );
                CREATE TABLE \"oauth_clients\" (
                  \"client_id\" text,
                  \"client_secret\" text,
                  \"redirect_uri\" text
                );
                CREATE TABLE \"oauth_refresh_tokens\" (
                  \"refresh_token\" text,
                  \"client_id\" text,
                  \"user_id\" text,
                  \"expires\" timestamp NOT NULL ,
                  \"scope\" text
                );
                CREATE TABLE \"users\" (
                  \"id\"  INTEGER PRIMARY KEY AUTOINCREMENT,
                  \"email\" varchar(100) NOT NULL DEFAULT '',
                  \"password\" varchar(255) NOT NULL DEFAULT '',
                  \"salt\" varchar(255) NOT NULL DEFAULT '',
                  \"roles\" varchar(255) NOT NULL DEFAULT '',
                  \"name\" varchar(100) NOT NULL DEFAULT '',
                  \"time_created\" int(11) NOT NULL DEFAULT '0'
                );
                CREATE INDEX \"users_unique_email\" ON \"users\" (\"email\");
                END TRANSACTION;");
    }
    catch (PDOException $e){
            echo "PDO Error: ".$e->getMessage(); 
    }
}else{
  //mysql
  try {
    $db = new PDO(
                  $params['dsn'],
                  $params['user'],
                  $params['password']
                  );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //drop database if exists
    $db->exec('CREATE DATABASE IF NOT EXISTS `'.$params['dbname'].'`');
    $db->exec('USE `'.$params['dbname'].'`');
    //drop tables if exists
    $db->exec('DROP TABLE IF EXISTS oauth_clients');
    $db->exec('DROP TABLE IF EXISTS oauth_access_tokens ');
    $db->exec('DROP TABLE IF EXISTS oauth_authorization_codes');
    $db->exec('DROP TABLE IF EXISTS oauth_refresh_tokens');
    $db->exec('DROP TABLE IF EXISTS users');

    //create tables (for oAuth server and user management)
    $db->exec('CREATE TABLE oauth_clients (client_id TEXT, client_secret TEXT, redirect_uri TEXT)');
    $db->exec('CREATE TABLE oauth_access_tokens (access_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');
    $db->exec('CREATE TABLE oauth_authorization_codes (authorization_code TEXT, client_id TEXT, user_id TEXT, redirect_uri TEXT, expires TIMESTAMP, scope TEXT)');
    $db->exec('CREATE TABLE oauth_refresh_tokens (refresh_token TEXT, client_id TEXT, user_id TEXT, expires TIMESTAMP, scope TEXT)');
    $db->exec("CREATE TABLE `users` (`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,`email` VARCHAR(100) NOT NULL DEFAULT '',`password` VARCHAR(255) NOT NULL DEFAULT '',`salt` VARCHAR(255) NOT NULL DEFAULT '',`roles` VARCHAR(255) NOT NULL DEFAULT '',`name` VARCHAR(100) NOT NULL DEFAULT '',`time_created` INT NOT NULL DEFAULT 0,PRIMARY KEY (`id`),UNIQUE KEY `unique_email` (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

  }
  catch (PDOException $e){
    echo "PDO creation Error: ".$e->getMessage(); 
  }
}


try {
  
    // add fake test client app
    $db->exec("INSERT INTO `oauth_clients` (client_id, client_secret) VALUES ('demoapp', 'demopass')");
    $db->exec("INSERT INTO `oauth_clients` (`client_id` ,`client_secret`) VALUES ('cortext-dashboard',  'c0rt3xt')");
    echo "\nDatabase ".$params['dbname']." successfully created.\n";
}catch (PDOException $e){
        echo "PDO Error: ".$e->getMessage(); 
}





//chmod($dir, 0777);
// $db->exec('INSERT INTO oauth_access_tokens (access_token, client_id) VALUES ("testtoken", "Some Client")');
// $db->exec('INSERT INTO oauth_authorization_codes (authorization_code, client_id) VALUES ("testcode", "Some Client")');
