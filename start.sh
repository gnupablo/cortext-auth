#!/bin/bash
cd $PWD/server
php -S 0.0.0.0:29100 -t web web/index.php &> log/php.log

