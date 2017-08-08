<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '23.16.26.235', '24.86.40.31', '162.219.176.101', '158.85.122.228', '206.116.49.142', '54.218.217.232','24.84.45.15','70.197.80.176','70.197.68.46','70.197.70.147','104.152.233.75','70.211.145.160','104.174.121.222','70.197.69.17','173.183.27.49','70.79.50.163','108.47.15.14','70.211.134.189','199.116.75.130','12.35.79.25','::1','207.216.41.143','207.216.41.188','207.216.41.189')) || php_sapi_name() === 'cli-server')
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.'.$_SERVER['REMOTE_ADDR']);
}

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
Debug::enable();

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
