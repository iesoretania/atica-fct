#!/usr/bin/env bash

export DEBIAN_FRONTEND=noninteractive
echo 'Instalando componentes'
echo '======================'

echo 'nodejs'
curl -sL https://deb.nodesource.com/setup_4.x | sudo -E bash -
# apt-get -q update

echo 'phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2' | debconf-set-selections
apt-get -q -y install apache2 php5 mysql-server mysql-client phpmyadmin wget php5-intl php5-mcrypt php5-xdebug php5-curl nodejs

echo 'PATH=$PATH:/vagrant/node_modules/.bin
' >> /home/vagrant/.profile

echo 'Configurando Apache2'
echo '===================='
service apache2 stop
sed -i -e "s/www\-data/vagrant/" /etc/apache2/envvars
sed -i -e "s/var\/www\/html/vagrant\/web/" /etc/apache2/sites-available/000-default.conf
sed -i -e "s/var\/www/vagrant\/web/" /etc/apache2/apache2.conf
sed -i -e "s/AllowOverride None/AllowOverride All/" /etc/apache2/apache2.conf
echo 'EnableSendfile off' > /etc/apache2/conf-available/sendfile.conf
a2enconf sendfile
a2enmod rewrite

echo 'Configurando phpMyAdmin'
echo '======================='
echo '<?php
$cfg["Servers"][1]["AllowNoPassword"] = TRUE;
' > /etc/phpmyadmin/conf.d/passwordless.inc.php

echo 'Configurando PHP'
echo '================'
echo '
display_errors=on
date.timezone=Europe/Madrid
realpath_cache_size=2M

[XDebug]
xdebug.default_enable = 1
xdebug.idekey = "default"
xdebug.remote_enable = 1
xdebug.remote_autostart = 1
xdebug.remote_port = 9000
xdebug.remote_handler=dbgp
xdebug.max_nesting_level=250
' > /etc/php5/mods-available/symfony.ini
php5enmod mcrypt
php5enmod symfony
service apache2 restart

echo 'Instalando Composer y el instalador Symfony'
echo '==========================================='
wget -nv https://getcomposer.org/composer.phar
chmod +x composer.phar
mv composer.phar /usr/local/bin/composer
curl -LsS http://symfony.com/installer -o /usr/local/bin/symfony
chmod +x /usr/local/bin/symfony
#su - vagrant -c "cd /vagrant; composer install"

echo 'Instalando componentes de npm'
echo '============================='
npm install -g gulp
su - vagrant -c "cd /vagrant; npm install"

