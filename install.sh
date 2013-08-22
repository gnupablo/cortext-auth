#!/bin/bash
echo "installing composer packages..."
cd $PWD/server
COMPOSER_PROCESS_TIMEOUT=4000 composer update --prefer-dist
echo "...done. Cloning into user repo...";
cd vendor/cortext
git clone git@github.com:cortext/silex-simpleuser.git
echo "...done. Rebuilding database..."
cd ../..
php data/rebuild_db.php
echo "...done. Install complete. Don't forget to set up your web server to /web and maybe hour /etc/hosts if you use a local domain name."

