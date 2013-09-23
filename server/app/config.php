<?php

/** load the parameters configuration */
$parameterFile = __DIR__ . '/../data/parameters.json';
if (!file_exists($parameterFile))
{
    // allows you to customize parameter file
    $parameterFile = $parameterFile . '.dist';
}

$app['environments'] = array();
if (!$parameters = json_decode(file_get_contents($parameterFile), true))
{
    exit('unable to parse parameters file: ' . $parameterFile);
}

// we are using an array of configurations
if (!isset($parameters['client_id']))
{
    $app['environments'] = array_keys($parameters);
    $env = $app['session']->get('config_environment');
    $parameters = isset($parameters[$env]) ? $parameters[$env] : array_shift($parameters);
}


//db dsn     
if($parameters['db_options']['driver']=='pdo_sqlite')
{

    // determine where the sqlite DB will go
    $dbDir =  __DIR__.'/../data';
    $dbfile = $dbDir.'/'.$parameters['db_options']['dbname'];

    $parameters['db_options']['dsn'] = sprintf('sqlite://%s', $dbfile);
}
elseif($parameters['db_options']['driver']=='pdo_mysql'){

    $parameters['db_options']['dsn'] = 'mysql:host='.$parameters['db_options']['host'].';dbname='.$parameters['db_options']['dbname'];

}


$app['parameters'] = $parameters;
