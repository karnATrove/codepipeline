<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

/**
 * Check if a given ip is in a network
 * @param  string $ip    IP to check in IPV4 format eg. 127.0.0.1
 * @param  string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
 * @return boolean true if the ip is in this range / false if not.
 */
function ip_in_range( $ip, $range ) {
  if ( strpos( $range, '/' ) == false ) {
    $range .= '/32';
  }
  // $range is in IP/CIDR format eg 127.0.0.1/24
  list( $range, $netmask ) = explode( '/', $range, 2 );
  $range_decimal = ip2long( $range );
  $ip_decimal = ip2long( $ip );
  $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
  $netmask_decimal = ~ $wildcard_decimal;
  return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
}

$ip = $_SERVER['REMOTE_ADDR'];
$in_range = false;
$cidr_list = array("10.1.42.0/24","10.1.44.0/24","20.1.42.0/24","20.1.44.0/24","30.1.42.0/24","30.1.44.0/24","10.0.42.0/24","10.0.44.0/24","20.0.42.0/24","20.0.44.0/24","30.0.42.0/24","30.0.44.0/24");
foreach($cidr_list as $cidr) {
  $in_range = $in_range || (ip_in_range($ip,$cidr));
  if($in_range) { break; }
}


// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !(in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '23.16.26.235', '24.86.40.31', '162.219.176.101', '158.85.122.228', '206.116.49.142', '54.218.217.232','24.84.45.15','70.197.80.176','70.197.68.46','70.197.70.147','104.152.233.75','70.211.145.160','104.174.121.222','70.197.69.17','173.183.27.49','70.79.50.163','108.47.15.14','70.211.134.189','199.116.75.130','12.35.79.25','::1','207.216.41.143','207.216.41.188','207.216.41.189')) || php_sapi_name() === 'cli-server')
    || !($in_range))
{
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information. IP: '.$_SERVER['REMOTE_ADDR']);
} else {
  print_r(($_SERVER['REMOTE_ADDR'].' is eligible'),TRUE);
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
