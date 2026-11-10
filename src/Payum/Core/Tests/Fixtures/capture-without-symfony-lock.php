<?php

declare(strict_types=1);

// Runs a capture in a process where symfony/lock cannot be loaded, as if it were not installed.

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Tests\AcmeConfig;

require $argv[1];
require __DIR__ . '/../CommandDispatchTest.php';

spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Symfony\\Component\\Lock\\')) {
        throw new LogicException($class . ' is not installed.');
    }
}, true, true);

$_SERVER = [
    'HTTP_HOST' => 'payum.dev',
];

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig('sk_test'))
    ->getPayum();

$payment = new Payment();
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(123);
$payum->getStorage(Payment::class)->update($payment);

$token = $payum->getTokenFactory()->createCaptureToken('acme', $payment, 'https://payum.dev/done');

echo $payum->getGateway('acme')->execute(CaptureCommand::forToken($token))::class;
