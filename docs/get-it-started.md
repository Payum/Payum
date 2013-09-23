# Get it started.
In this chapter we are going to talk about the most common task: purchasing a product.
I would use paypal express checkout for example because it is popular.
All examples are written in plain php code (no frameworks).

_**Note**: If you are working with [symfony2]() framework check the payum bundle documentation instead._

![How payum works](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgcGF5cGFsLmNvbQoACwxVc2VyAAQNcHJlcGFyZS5waHAAHA1jYXB0dQAFE2RvbgAnBgpVc2VyLT4ANQs6AEUIIGEgcGF5bWVudAoAVAstLT4rAEsLOgBbCCB0b2tlbgoKAGcLLS0-AIE2CjogcmVxdWVzdCBhdXRoZW50aWNhdGlvbgoAgVkKLS0-AE0NZ2l2ZSBjb250cm9sIGJhY2sATg8tAIE-CDoAgUsFAHsHAIFTCC0-VXNlcjogc2hvdwCBQQggcmVzdWx0Cg&s=default)

## Configuration.

Before we look at `prepare.php` we have to configure payum:

```php
<?php
//config.php

use Buzz\Client\Curl;
use Payum\Extension\StorageExtension;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Registry\SimpleRegistry;
use Payum\Storage\FilesystemStorage;
use Payum\Security\PlainHttpRequestVerifier;

$tokenStorage = new FilesystemStorage('/path/to/storage', 'Payum/Model/Token', 'hash');
$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

// You way want to modify it to suite your needs
$paypalPaymentDetailsClass = 'Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails';
$storages = array(
    'paypal' => array(
        $paypalPaymentDetailsClass => new FilesystemStorage('/path/to/storage', $paypalPaymentDetailsClass, 'id')
    )
);

$payments = array(
    'paypal' => PaymentFactory::create(new Api(new Curl, array(
       'username' => 'REPLACE WITH YOURS',
       'password' => 'REPLACE WITH YOURS',
       'signature' => 'REPLACE WITH YOURS',
       'sandbox' => true
    )
)));

$payments['paypal']->addExtension(new StorageExtension($storages['paypal'][$paypalPaymentDetailsClass]));

$registry = new SimpleRegistry($payments, $storages, null, null);
```

TODO: add some words about code above.

_**Note**: Consider using something other than `FilesystemStorage` in production. `DoctrineStorage` may be a good alternative._

_**Note**: You are not required to use this PaymentDetails. Payum is designed to work with array or ArrayAccess._

## Prepare payment.

```php
<?php
// prepare.php

include 'config.php';

$storage = $registry->getStorageForClass($paypalPaymentDetailsClass, 'paypal');

$paymentDetails = $storage->createModel();
$paymentDetails['PAYMENTREQUEST_0_CURRENCYCODE'] = 'EUR';
$paymentDetails['PAYMENTREQUEST_0_AMT'] = 1.23;
$storage->updateModel($paymentDetails);

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('paypal');
$doneToken->setDetails($storage->getIdentifier($paymentDetails));
$doneToken->setTargetUrl($_SERVER['HTTP_HOST'].'/done.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($doneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('paypal');
$captureToken->setDetails($storage->getIdentifier($paymentDetails));
$captureToken->setTargetUrl($_SERVER['HTTP_HOST'].'/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($doneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

header("Location: ".$captureToken->getTargetUrl());
```

TODO: add some words about code above.

The main purpose of using tokens to hide any sensitive\guessable information from a user.
A user see is the random hash so it would be a bit hard to hack your payment.

## Capture payment.

If you read the previous chapter carefully you may notice `capture.php` script we set it as the target url of capture token.
At the last lines of `prepare.php` we delegated the job to `capture.php` script.
This file is designed to be reused by any possible payment.
So if you don`t want dive into details just [copy\past it](capture-action.md).

## Show payment status (done.php).

After the capture did its job you will be redirected to `done.php`.
The `capture.php` script always redirects you to `done.php` no matter payment was success or not.
In `done.php` we should check payment status and act on it result.

```php
<?php
include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new BinaryMaskStatusRequest($token));
if ($status->isSuccess()) {
    echo 'payment captured successfully';
} else {
    echo 'payment captured not successfully';
}
```

_**Note**: Success is not only one status available. There are other statuses present: pending, failure, canceled etc._

Back to [index](index.md).
