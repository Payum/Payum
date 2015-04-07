<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    echo <<<EOM
You must set up the project dependencies by running the following commands:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

EOM;

    exit(1);
}

$rc = new \ReflectionClass('Payum\Core\GatewayInterface');
$coreDir = dirname($rc->getFileName()).'/Tests';

$loader->add('Payum\Core\Tests', $coreDir);
$loader->add('Payum\Payex\Tests', __DIR__);
