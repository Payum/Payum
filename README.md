Payum Paypal ExpressCheckout Nvp
================================

The lib implements [Paypal Express Checkout](https://www.x.com/content/paypal-nvp-api-overview) payment. 

Create api
----------
```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::createApi()

$client = new \Buzz\Client\Curl;
$client->setTimeout(30000);

$api = new \Payum\Paypal\ExpressCheckout\Nvp\Api($client, array(
    'username' => 'a_username',
    'password' => 'a_pasword',
    'signature' => 'a_signature',
    'return_url' => 'a_return_url',
    'cancel_url' => 'a_return_url',
    'sandbox' => true
));
```

Create payment:
--------------
```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::createPayment()

//...

$payment = new \Payum\Paypal\ExpressCheckout\Nvp\Payment($api);

$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SetExpressCheckoutAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\GetExpressCheckoutDetailsAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\GetTransactionDetailsAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SyncAction());
```

Do simple sell:
--------------
```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doSell()

//...

$instruction = new \Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
$instruction->setPaymentrequestAmt(0, 100);
$instruction->setPaymentrequestCurrencycode(0, 'USD');

if ($interactiveRequest = $payment->execute(new \Payum\Request\CaptureRequest($instruction))) {
    if ($interactiveRequest instanceof \Payum\Request\RedirectUrlInteractiveRequest) {
        echo 'Paypal requires the user be redirected to: '.$interactiveRequest->getUrl();
    }
}

$statusRequest = new \Payum\Request\BinaryMaskStatusRequest($instruction);
$payment->execute($statusRequest);
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