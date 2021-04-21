<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    echo <<<EOM
You must set up the project dependencies by running the following commands:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install

EOM;

    exit(1);
}

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

$rc = new \ReflectionClass('Payum\Core\GatewayInterface');
$coreDir = dirname($rc->getFileName()).'/Tests';

$loader->add('Payum\Core\Tests', $coreDir);
