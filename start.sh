#!/bin/bash
cd $PWD/server
php -S localhost:29100 -t web web/index.php &> log/php.log

