#!/bin/bash
service httpd restart > /var/www/vhosts/log/restartapache.out 2>&1
