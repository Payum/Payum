<?php
if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    echo <<<EOM
You must set up the project dependencies by running the following commands:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install --dev

EOM;

    exit(1);
}

$loader->add('Payum\Klarna\Invoice\Tests', __DIR__);
$loader->add('Payum\Core\Tests', __DIR__.'/../vendor/payum/payum/tests');