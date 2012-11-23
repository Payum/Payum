Payum Paypal ExpressCheckout Nvp
================================

The lib implements [Paypal Express Checkout](https://www.x.com/content/paypal-nvp-api-overview) payment. 

Create api
----------
```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::createApi()

$api = new \Payum\Paypal\ExpressCheckout\Nvp\Api(new \Buzz\Client\Curl, array(
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

$payment = new \Payum\Paypal\ExpressCheckout\Nvp\Payment();

$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SetExpressCheckoutAction($api));
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\GetExpressCheckoutDetailsAction($api));
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction($api));
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SaleAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SimpleSellAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction());
$payment->addAction(new \Payum\Paypal\ExpressCheckout\Nvp\Action\SyncAction());
```

Do simple sell:
--------------
```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doSell()

//...

$sell = new \Payum\Request\SimpleSellRequest();
$sell->setPrice(100);
$sell->setCurrency('USD');

if ($interactiveRequest = $payment->execute($sell)) {
    if ($interactiveRequest instanceof \Payum\Request\RedirectUrlInteractiveRequest) {
        echo 'Paypal requires the user be redirected to: '.$interactiveRequest->getUrl();
    }
}

$statusRequest = new \Payum\Request\BinaryMaskStatusRequest($sell);
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