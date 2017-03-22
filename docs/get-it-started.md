# Get it started.

Here we describe basic steps required by all supported gateways. We are going to setup models, storages, a security layer and so on.
All that stuff will be used later.

_**Note**: If you are working with Symfony2 framework look read the bundle's [documentation](index.md#symfony-payum-bundle) instead._

_**Note**: If you are working with Laravel5 framework look read the bundle's [documentation](index.md#laravel-payum-package) instead._

## Install

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/offline php-http/guzzle6-adapter
```

_**Note**: Where payum/offline is a php payum extension, you can for example change it to payum/paypal-express-checkout-nvp or payum/stripe. Look at [supported gateways](supported-gateways.md) to find out what you can use._

_**Note**: Use payum/payum if you want to install all gateways at once._

_**Note**: Use php-http/guzzle6-adapter is just an example. You can use any of [these adapters](https://packagist.org/providers/php-http/client-implementation)._

Before we configure the payum let's look at the flow diagram.
This flow is same for all gateways so once you familiar with it any other gateways could be added easily.

![How payum works](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgcGF5cGFsLmNvbQoACwxVc2VyAAQNcHJlcGFyZS5waHAAHA1jYXB0dQAFE2RvbgAnBgpVc2VyLT4ANQs6AEUIIGEgcGF5bWVudAoAVAstLT4rAEsLOgBbCCB0b2tlbgoKAGcLLS0-AIE2CjogcmVxdWVzdCBhdXRoZW50aWNhdGlvbgoAgVkKLS0-AE0NZ2l2ZSBjb250cm9sIGJhY2sATg8tAIE-CDoAgUsFAHsHAIFTCC0-VXNlcjogc2hvdwCBQQggcmVzdWx0Cg&s=default)

As you can see we have to create some php files: `config.php`, `prepare.php`, `capture.php` and `done.php`.
At the end you will have the complete solution and 
it would be [much easier to add](paypal/express-checkout/get-it-started.md) other gateways.
Let's start from the `config.php` and continue with rest after:

## config.php

Here we can put our gateways, storages. Also we can configure security components. The `config.php` has to be included to all left files.

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;
use Payum\Core\Model\Payment;

$paymentClass = Payment::class;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('aGateway', [
        'factory' => 'offline',
    ])

    ->getPayum()
;
```

_**Note**: There are other [storages](storages.md) available. Such as Doctrine ORM\MongoODM._

_**Note**: Consider using something other than `FilesystemStorage` in production._

## prepare.php

At this stage we have to create an order. Add some information into it. 
Create a capture token and delegate the job to [capture.php](examples/capture-script.md) script.
Here's an offline gateway example:

```php
<?php
// prepare.php

include __DIR__.'/config.php';

$gatewayName = 'aGateway';

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage($paymentClass);

$payment = $storage->create();
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(123); // 1.23 EUR
$payment->setDescription('A description');
$payment->setClientId('anId');
$payment->setClientEmail('foo@example.com');

$payment->setDetails(array(
  // put here any fields in a gateway format.
  // for example if you use Paypal ExpressCheckout you can define a description of the first item:
  // 'L_PAYMENTREQUEST_0_DESC0' => 'A desc',
));


$storage->update($payment);

$captureToken = $payum->getTokenFactory()->createCaptureToken($gatewayName, $payment, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

_**Note**: There are examples for all [supported gateways](supported-gateways.md)._

## capture.php

When the preparation is done a user is redirect to `capture.php`. Here's an example of this file. You can just copy\past the code. 
It has to work for all gateways without any modification from your side. 

```php
<?php
//capture.php

use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;

include __DIR__.'/config.php';

/** @var \Payum\Core\GatewayInterface $gateway */
if ($reply = $gateway->execute(new Capture($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        die();
    }

    throw new \LogicException('Unsupported reply', null, $reply);
}

/** @var \Payum\Core\Payum $payum */
$payum->getHttpRequestVerifier()->invalidate($token);

header("Location: ".$token->getAfterUrl());
```

_**Note**: Find out more about capture script in the [dedicated chapter](examples/capture-script.md)._

## done.php

After the capture did its job you will be redirected to [done.php](examples/done-script.md).
The [capture.php](examples/capture-script.md) script always redirects you to `done.php` no matter the payment was a success or not.
In `done.php` we may check the payment status, update the model, dispatch events and so on.

```php
<?php
// done.php

use Payum\Core\Request\GetHumanStatus;

include __DIR__.'/config.php';

/** @var \Payum\Core\Payum $payum */
$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

// you can invalidate the token. The url could not be requested any more.
// $payum->getHttpRequestVerifier()->invalidate($token);

// Once you have token you can get the model from the storage directly. 
//$identity = $token->getDetails();
//$payment = $payum->getStorage($identity->getClass())->find($identity);

// or Payum can fetch the model for you while executing a request (Preferred).
$gateway->execute($status = new GetHumanStatus($token));
$payment = $status->getFirstModel();

header('Content-Type: application/json');
echo json_encode([
    'status' => $status->getValue(),
    'order' => [
        'total_amount' => $payment->getTotalAmount(),
        'currency_code' => $payment->getCurrencyCode(),
        'details' => $payment->getDetails(),
    ],
]);
```

_**Note**: Find out more about done script in the [dedicated chapter](examples/done-script.md)._

Back to [index](index.md).
