#!/bin/bash
cd $PWD/server
COMPOSER_PROCESS_TIMEOUT=4000 composer update --prefer-dist

php data/rebuild_db.php