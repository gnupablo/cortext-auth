FROM ubuntu:latest
MAINTAINER webmaster@cortext.fr
RUN echo "deb http://archive.ubuntu.com/ubuntu saucy main universe" > /etc/apt/sources.list
RUN apt-get update
RUN apt-get upgrade -y
# https://github.com/dotcloud/docker/issues/1024
RUN dpkg-divert --local --rename --add /sbin/initctl
RUN ln -s /bin/true /sbin/initctl
 
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y -q mysql-server
 
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y php5-cli php5-mysql php5-curl php5-sqlite
RUN DEBIAN_FRONTEND=noninteractive apt-get install -y git curl openssh-server apache2 supervisor

RUN mkdir -p /var/run/sshd
RUN mkdir -p /var/log/supervisor
RUN mkdir /server
 
RUN git clone https://github.com/cortext/cortext-auth.git /server/cortext-auth
RUN git clone https://github.com/cortext/silex-simpleuser.git /server/cortext-auth/server/vendor/cortext/silex-simpleuser
RUN cp /server/cortext-auth/apache2.conf /etc/apache2/sites-available/cortext.conf
RUN a2ensite cortext
WORKDIR /server/cortext-auth
RUN curl -s http://getcomposer.org/installer | php
RUN ./composer.phar install
 
WORKDIR /server/cortext-auth/server
RUN ../composer.phar update

RUN cd /server/cortext-auth/server ; php data/rebuild_db.php
 
EXPOSE 22 80
CMD ["/usr/bin/supervisord"]
 
# CMD php -S 0.0.0.0:29100 -t web web/index.php