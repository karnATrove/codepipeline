#!/bin/bash

# exec 3>&1 4>&2
# trap 'exec 2>&4 1>&3' 0 1 2 3
# exec 1>/var/www/vhosts/logs/out.log 2>&1
chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/cache
chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/logs
chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/cache
chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/logs
chown -R ec2-user:apache /var/www
chown -R ec2-user:apache /etc/httpd/conf
chown -R ec2-user:apache /etc/httpd/conf.d
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
ln -s /usr/local/bin/composer /usr/bin/composer
cd /var/www/vhosts/rlogistic.roveconcepts.me/public_html/app/config
if [ -e parameters.yml ]
then
    echo "ok parameters.yml or rlogistic exist"
else
    cd /var/www/vhosts/rlogistic.roveconcepts.me/public_html/web
    #composer install
fi
cd /var/www/vhosts/dx3pl.roveconcepts.me/public_html/app/config
if [ -e parameters.yml ]
then
    echo "ok parameters.yml or dx3pl exist"
else
    cd /var/www/vhosts/dx3pl.roveconcepts.me/public_html/web
    #composer install
fi
#composer update
#php -d memory_limit=2048M bin/console cache:clear --env=prod
#php -d memory_limit=2048M bin/console assetic:dump
#cat /var/www/vhosts/rlogistic.roveconcepts.me/public_html/app/config/parameters.yml | grep database_host
yum install perl-Switch perl-DateTime perl-Sys-Syslog perl-LWP-Protocol-https -y
mkdir /CloudWatch
cd /CloudWatch
curl http://aws-cloudwatch.s3.amazonaws.com/downloads/CloudWatchMonitoringScripts-1.2.1.zip -O
unzip CloudWatchMonitoringScripts-1.2.1.zip
rm -f CloudWatchMonitoringScripts-1.2.1.zip
#cd aws-scripts-mon
(crontab -l ; echo "*/5 * * * * ~/aws-scripts-mon/mon-put-instance-data.pl --mem-used-incl-cache-buff --mem-util --disk-space-util --disk-path=/ --from-cron") | crontab
#./mon-put-instance-data.pl --mem-util --mem-used-incl-cache-buff --mem-used --mem-avail
#./mon-put-instance-data.pl --mem-util --mem-used --mem-avail --auto-scaling=only
#./mon-put-instance-data.pl --mem-util --mem-used --mem-avail --aggregated=only
#chown -R ec2-user:apache /var/www
#chmod -R 777 /var/www/
#chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/cache
#chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/logs
chown -R ec2-user:apache /var/www
chmod -R 777 /var/www/
chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/cache
chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/logs
chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/cache
chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/logs
service httpd restart > /var/www/vhosts/logs/restartapache.out 2>&1
