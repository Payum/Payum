
Be2Bill
=======

The lib implements [Be2Bill](http://www.be2bill.com/) payment. 

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$api = new \Payum\Be2Bill\Api(new \Buzz\Client\Curl(), array(
    'identifier' => 'foo',
    'password' => 'bar',
    'sandbox' => true
));

$payment = new \Payum\Be2Bill\Payment($api);
$payment->addAction(new \Payum\Be2Bill\Action\CaptureAction);
$payment->addAction(new \Payum\Be2Bill\Action\StatusAction);

$instruction = new \Payum\Be2Bill\PaymentInstruction();
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

$captureRequest = new \Payum\Request\CaptureRequest($instruction);
if ($interactiveRequest = $payment->execute($captureRequest)) {
    throw $interactiveRequest;
}

$statusRequest = new \Payum\Request\BinaryMaskStatusRequest($instruction);
if ($interactiveRequest = $payment->execute($statusRequest)) {
    throw $interactiveRequest;
}

if ($statusRequest->isSuccess()) {
    //We are done!
} else if ($statusRequest->isCanceled()) {
    //Canceled!
} elseif ($statusRequest->isFailed()) {
    //Failed
} elseif ($statusRequest->isInProgress()) {
    //In progress!
} elseif ($statusRequest->isUnknown()) {
    //Status unknown!
}
```