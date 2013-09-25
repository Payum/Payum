<?php
/*
 * Sample bootstrap file.
*/

// Include the composer autoloader
if(!file_exists(__DIR__ .'/../vendor/autoload.php')) {
	echo "The 'vendor' folder is missing. You must run 'composer update' to resolve application dependencies.\nPlease see the README for more information.\n";
	exit(1);
}
require __DIR__ . '/../vendor/autoload.php';
use PayPal\Auth\OAuthTokenCredential;

define("PP_CONFIG_PATH", __DIR__);

$configManager = \PPConfigManager::getInstance();

// $cred is used by samples that include this bootstrap file
// This piece of code simply demonstrates how you can
// dynamically pass in a client id/secret instead of using
// the config file. If you do not need a way to pass
// in credentials dynamically, you can skip the
// <Resource>::setCredential($cred) calls that
// you see in the samples.
$cred = new OAuthTokenCredential(
		$configManager->get('acct1.ClientId'),
		$configManager->get('acct1.ClientSecret'));


/**
 * ### getBaseUrl function
 * // utility function that returns base url for
 * // determining return/cancel urls
 * @return string
 */
function getBaseUrl() {

	$protocol = 'http';
	if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
		$protocol .= 's';
		$protocol_port = $_SERVER['SERVER_PORT'];
	} else {
		$protocol_port = 80;
	}

	$host = $_SERVER['HTTP_HOST'];
	$port = $_SERVER['SERVER_PORT'];
	$request = $_SERVER['PHP_SELF'];
	return dirname($protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $request);
}
