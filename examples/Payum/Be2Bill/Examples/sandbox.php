<?php
require_once __DIR__ . '/vendor/autoload.php';

$api = new \Payum\Be2Bill\Api(new \Buzz\Client\Curl(), array(
    'identifier' => 'REMIXJOB',
    'password' => '{=Tk<%V}WY]L(haB',
    'sandbox' => true
));

$payment = new \Payum\Be2Bill\Payment();
$payment->addAction(new \Payum\Be2Bill\Action\CaptureAction($api));
$payment->addAction(new \Payum\Be2Bill\Action\StatusAction());

$sell = new \Payum\Domain\SimpleSell();
$sell->setPrice(10);
$sell->setCurrency('EUR');
$sell->setInstruction($instruction = new \Payum\Be2Bill\PaymentInstruction());
$instruction->setAmount(10);
$instruction->setClientuseragent('Firefox');
$instruction->setClientip('82.117.234.33');
$instruction->setClientident('anIdent');
$instruction->setClientemail('test@example.com');
$instruction->setCardcode('4111111111111111');
$instruction->setDescription('aDescr');
$instruction->setOrderid('anId');
$instruction->setCardfullname('John Doe');
$instruction->setCardvaliditydate('10-13');
$instruction->setCardcvv('123');

$payment->execute(new \Payum\Request\CaptureRequest($sell));
$payment->execute($statusRequest = new \Payum\Request\BinaryMaskStatusRequest($sell));

var_dump($instruction, $statusRequest);
die;