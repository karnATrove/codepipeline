#!/bin/bash

# exec 3>&1 4>&2
# trap 'exec 2>&4 1>&3' 0 1 2 3
# exec 1>/var/www/vhosts/logs/out.log 2>&1
chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/cache
chmod -R 777 /var/www/vhosts/rlogistic.roveconcepts.me/public_html/var/logs
service httpd restart > /var/www/vhosts/logs/restartapache.out 2>&1
