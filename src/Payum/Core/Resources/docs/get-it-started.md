# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product using [Paypal Express Checkout](https://developer.paypal.com/webapps/developer/docs/classic/express-checkout/integration-guide/ECGettingStarted/).
I would use paypal express checkout for example because it is the most popular as I see.
All examples are written in plain php code (no frameworks).

_**Note**: If you are working with symfony2 framework look at the bundle [documentation instead](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md)._

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/paypal-express-checkout-nvp:*@stable"
```

## Configuration

Before we configure the payum let's look at the flow diagram.
This flow is same all payments so once you familiar with it any other payments could be added easily.

![How payum works](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgcGF5cGFsLmNvbQoACwxVc2VyAAQNcHJlcGFyZS5waHAAHA1jYXB0dQAFE2RvbgAnBgpVc2VyLT4ANQs6AEUIIGEgcGF5bWVudAoAVAstLT4rAEsLOgBbCCB0b2tlbgoKAGcLLS0-AIE2CjogcmVxdWVzdCBhdXRoZW50aWNhdGlvbgoAgVkKLS0-AE0NZ2l2ZSBjb250cm9sIGJhY2sATg8tAIE-CDoAgUsFAHsHAIFTCC0-VXNlcjogc2hvdwCBQQggcmVzdWx0Cg&s=default)

Now configuration. Let's  start from defining some models.
First one is a `PaymentDetails`.
It will storage all the information related to the payment:

```php
<?php
namespace App\Model;

use Payum\Core\Model\ArrayObject;

class PaymentDetails extends ArrayObject
{
}
```

The other one is `PaymentSecurityToken`.
We will use it to secure our payment operations:

```php
<?php
namespace App\Model;

use Payum\Core\Model\Token;

class PaymentSecurityToken extends Token
{
}
```

_**Note**: We provide Doctrine ORM\MongoODM mapping for these models to ease usage with doctrine storage._

Now we are ready to configure all the stuff:

```php
<?php
//config.php

use Buzz\Client\Curl;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Security\PlainHttpRequestVerifier;
use Payum\Core\Security\GenericTokenFactory;

$tokenStorage = new FilesystemStorage('/path/to/storage', 'App\Model\PaymentSecurityToken', 'hash');
$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

$detailsClass = 'App\Model\PaymentDetails';

$storages = array(
    'paypal' => array(
        $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass)
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

$registry = new SimpleRegistry($payments, $storages);

$tokenFactory = new GenericTokenFactory(
    $tokenStorage,
    $registry,
    'http://'.$_SERVER['HTTP_HOST'],
    'capture.php',
    'notify.php'
);
```

An initial configuration for payum basically wants to ensure we have things ready to be stored such as
a token, to identify our payment process. A request verifier will take that token and be also initialized.
We also would like to have a registry of various payment mechanisms supported and the place where they are going
to be storing their information (e.g. payment details).

_**Note**: Consider using something other than `FilesystemStorage` in production. `DoctrineStorage` may be a good alternative._

_**Note**: You are not required to use this PaymentDetails. Payum is designed to work with array or ArrayAccess._

## Prepare payment

```php
<?php
// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$paymentDetails = $storage->createModel();
$paymentDetails['PAYMENTREQUEST_0_CURRENCYCODE'] = 'EUR';
$paymentDetails['PAYMENTREQUEST_0_AMT'] = 1.23;
$storage->updateModel($paymentDetails);

$captureToken = $tokenFactory->createCaptureToken('paypal', $paymentDetails, 'done.php');
$paymentDetails['RETURNURL'] = $captureToken->getTargetUrl();
$paymentDetails['CANCELURL'] = $captureToken->getTargetUrl();
$storage->updateModel($paymentDetails);

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

With a basic configuration now we proceed to update payment details, use the payment details stored
reference to create and relate to it two tokens, one is the done token, and the other is the capture token.
We relate the tokens back to the payment details by assigning its return and cancel urls which now contain
specific hashes from the tokens. After all is prepared, finally we start the capturing process.

The main purpose of using tokens is to hide any sensitive\guessable information from a spying user.
All a spying user sees is the random hash so it would be a bit hard to hack your payment process.

_**Attention**: All sensitive values must not be passed directly but wrapped by `SensitiveValue` class. That's required to ensure it would not accidentally be saved anywhere. For more info read [dedicated chapter](working-with-sensitive-information.md)._

## Capture payment

If you read the previous chapter carefully you may noticed that in the `prepare.php` script we set
`capture.php` as the target url of capture token.
On the last lines of `prepare.php` we delegated the job to `capture.php` script.
This file is designed to be reused by any possible payment process.
So if you don't want to dive into details just [copy\paste it](capture-script.md).

## Show payment status (done.php)

After the capture did its job you will be redirected to `done.php`.
The `capture.php` script always redirects you to `done.php` no matter the payment was a success or not.
In `done.php` we will check the payment status and act on its result.

```php
<?php
include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new SimpleStatusRequest($token));
if ($status->isSuccess()) {
    //Do your business tasks here

    echo 'payment captured successfully';
} else {
    echo 'payment captured not successfully';
}
```

_**Note**: Success is not the only one status available. There are other statuses possible: pending, failure, canceled etc._

Next [The architecture](the-architecture.md).

Back to [index](index.md).
