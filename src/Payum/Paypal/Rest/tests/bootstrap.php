<?php
ini_set('display_errors', 1);
error_reporting(-1);

if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    echo <<<EOM
You must set up the project dependencies by running the following commands:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install --dev

EOM;

    exit(1);
}

$loader->add('Payum\Paypal\Rest\Tests', __DIR__);
$loader->add('Payum\Tests', __DIR__.'/../vendor/payum/payum/tests');