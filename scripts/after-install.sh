#!/bin/bash

# exec 3>&1 4>&2
# trap 'exec 2>&4 1>&3' 0 1 2 3
# exec 1>/var/www/vhosts/logs/out.log 2>&1
#chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/cache
#chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/logs
#chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/cache
#chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/logs

#######################################################################
# Change user and group of some directories
#######################################################################
chown -R ec2-user:apache /var/www
chown -R ec2-user:apache /etc/httpd/conf
chown -R ec2-user:apache /etc/httpd/conf.d

###############################################################################
# Change parameter in php.ini, this must be done before clearing composer cache
###############################################################################
sed -i 's/memory_limit = 128M/memory_limit = 2048M/g' /etc/php.ini
cat /etc/php.ini | grep 'memory_limit' >> /var/www/vhosts/logs/after_install_script.out 2>&1
service httpd restart >> /var/www/vhosts/logs/after_install_script.out 2>&1
#sudo sed -i 's/128M/2048M/g' /etc/php.ini

#######################################################################
# Install composer
# Execute composer install if parameters.yml file is not yet created
#######################################################################
cd /etc/profile.d/
chmod +x /etc/profile.d/configuration.sh
./configuration.sh
cd ~
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
ln -s /usr/local/bin/composer /usr/bin/composer
cd /var/www/vhosts/rlogistic.roveconcepts.me/public_html/app/config
if [ -e parameters.yml ]
then
    echo "parameters.yml of rlogistic already existed" >> /var/www/vhosts/logs/after_install_script.out 2>&1
else
    echo "no parameters.yml of rlogistic exist" >> /var/www/vhosts/logs/after_install_script.out 2>&1
    cd /var/www/vhosts/rlogistic.roveconcepts.me/public_html/web
    export database_name=rlogistic
    export business_name=rlogistic
    composer install --no-interaction
    cd /var/www/vhosts/rlogistic.roveconcepts.me/public_html
    echo "Clear rlogistic cache" >> /var/www/vhosts/logs/after_install_script.out 2>&1
    php -d memory_limit=2048M bin/console cache:clear --env=prod >> /var/www/vhosts/logs/after_install_script.out 2>&1
    php -d memory_limit=2048M bin/console assetic:dump >> /var/www/vhosts/logs/after_install_script.out 2>&1
fi

cd /var/www/vhosts/dx3pl.roveconcepts.me/public_html/app/config
if [ -e parameters.yml ]
then
    echo "parameters.yml of dx3pl already existed" >> /var/www/vhosts/logs/after_install_script.out 2>&1
else
    echo "no parameters.yml of dx3pl exist" >> /var/www/vhosts/logs/after_install_script.out 2>&1
    cd /var/www/vhosts/dx3pl.roveconcepts.me/public_html/web
    export database_name=dx3pl
    export business_name=dx3pl
    composer install --no-interaction
    cd /var/www/vhosts/dx3pl.roveconcepts.me/public_html
    echo "Clear dx3pl cache" >> /var/www/vhosts/logs/after_install_script.out 2>&1
    php -d memory_limit=2048M bin/console cache:clear --env=prod >> /var/www/vhosts/logs/after_install_script.out 2>&1
    php -d memory_limit=2048M bin/console assetic:dump >> /var/www/vhosts/logs/after_install_script.out 2>&1
fi
#cat /var/www/vhosts/rlogistic.roveconcepts.me/public_html/app/config/parameters.yml | grep database_host
#echo 'export database_host=rlogistic-cluster.cluster-cepwew4s61wr.us-west-2.rds.amazonaws.com' > /var/www/vhosts/config/configuration.sh


#################################################################################
# Install perl script
# Create custom cloudwatch metric and execute crontab to push data to CloudWatch
#################################################################################
yum install perl-Switch perl-DateTime perl-Sys-Syslog perl-LWP-Protocol-https -y
yum install zip unzip -y
mkdir /CloudWatch
cd /CloudWatch
curl http://aws-cloudwatch.s3.amazonaws.com/downloads/CloudWatchMonitoringScripts-1.2.1.zip -O
unzip CloudWatchMonitoringScripts-1.2.1.zip
rm -f CloudWatchMonitoringScripts-1.2.1.zip
crontab -l | grep -q 'aws-script'  && echo 'entry exists' || (crontab -l ; echo "*/5 * * * * ~/aws-scripts-mon/mon-put-instance-data.pl --mem-used-incl-cache-buff --mem-util --disk-space-util --disk-path=/ --from-cron") | crontab
#cd /CloudWatch/aws-scripts-mon
#(crontab -l ; echo "*/5 * * * * ~/aws-scripts-mon/mon-put-instance-data.pl --mem-used-incl-cache-buff --mem-util --disk-space-util --disk-path=/ --from-cron") | crontab
#./mon-put-instance-data.pl --mem-util --mem-used-incl-cache-buff --mem-used --mem-avail
#./mon-put-instance-data.pl --mem-util --mem-used --mem-avail --auto-scaling=only
#./mon-put-instance-data.pl --mem-util --mem-used --mem-avail --aggregated=only


#################################################################################
# Ensure to change user and group as well as grant accessing to directories 
# to prevent file permissions problem
#################################################################################
chown -R ec2-user:apache /var/www
chmod -R 777 /var/www/
chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/cache
chmod -R 777 /var/www/vhosts/dx3pl.roveconcepts.me/public_html/var/logs
chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/cache
chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/logs


#################################################################################
# Restart httpd
#################################################################################
echo "Finally restart Apache" >> /var/www/vhosts/logs/after_install_script.out 2>&1
service httpd restart >> /var/www/vhosts/logs/after_install_script.out 2>&1
