# Get it started.

Here we describe basic steps required by all supported payments. We are going to setup models, storages, a security layer and so on.
All that stuff will be used later.

_**Note**: If you are working with Symfony2 framework look read the bundle's [documentation](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md) instead._

_**Note**: If you are working with Laravel4 framework look read the bundle's [documentation](https://github.com/Payum/PayumLaravelPackage/blob/master/docs/index.md) instead._

## Install

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/offline:*@stable"
```

_**Note**: Where payum/offline is a php payum extension, you can for example change it to payum/paypal-express-checkout-nvp or payum/stripe. Look at [supported payments](supported-payments.md) to find out what you can use._

_**Note**: Use payum/payum if you want to install all payments at once._

Before we configure the payum let's look at the flow diagram.
This flow is same all payments so once you familiar with it any other payments could be added easily.

![How payum works](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgcGF5cGFsLmNvbQoACwxVc2VyAAQNcHJlcGFyZS5waHAAHA1jYXB0dQAFE2RvbgAnBgpVc2VyLT4ANQs6AEUIIGEgcGF5bWVudAoAVAstLT4rAEsLOgBbCCB0b2tlbgoKAGcLLS0-AIE2CjogcmVxdWVzdCBhdXRoZW50aWNhdGlvbgoAgVkKLS0-AE0NZ2l2ZSBjb250cm9sIGJhY2sATg8tAIE-CDoAgUsFAHsHAIFTCC0-VXNlcjogc2hvdwCBQQggcmVzdWx0Cg&s=default)

As you can see we have to create some php files: `config.php`, `prepare.php`, `capture.php` and `done.php`.
At the end you will have the complete payment solution and 
it would be much easier to add other payments.
Let's start from the config.php and continue with rest after:

## config.php

Here we can put our payments, storages. Also we can configure security components. The `config.php` has to be included to all left files.

```php
<?php
//config.php

use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Security\PlainHttpRequestVerifier;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Offline\PaymentFactory as OfflinePaymentFactory;

$orderClass = 'Payum\Core\Model\Order';

$storages = array(
    $orderClass => new FilesystemStorage('/path/to/storage', $orderClass, 'number'),
    
    //put other storages
);

$payments = array(
    'offline' => OfflinePaymentFactory::create(),
    
    //put other payments
);

$payum = new SimpleRegistry($payments, $storages);

//security

$tokenStorage = new FilesystemStorage('/path/to/storage', 'Payum\Core\Model', 'hash');

$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

$tokenFactory = new GenericTokenFactory(
    $tokenStorage,
    $registry,
    'http://'.$_SERVER['HTTP_HOST'],
    'capture.php',
    'notify.php',
    'authorize.php'
);
```

_**Note**: There are other [storages](storages.md) available. Such as Doctrine ORM\MongoODM._

_**Note**: Consider using something other than `FilesystemStorage` in production._

## prepare.php

At this stage we have to create an order. Add some information into it. 
Create a capture token and delegate the job to [capture.php](capture-script.md) script.
Here's an offline payment example:

```php
<?php
// prepare.php

include 'config.php';

$paymentName = 'offline';

$storage = $payum->getStorage($orderClass);

$order = $storage->createModel();
$order->setNumber(uniqid());
$order->setCurrencyCode('EUR');
$order->setTotalAmount(123); // 1.23 EUR
$order->setDescription('A description');
$order->setClientId('anId');
$order->setClientEmail('foo@example.com');

$storage->updateModel($order);

$captureToken = $tokenFactory->createCaptureToken($paymentName, $order, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

_**Note**: There are examples for all [supported payments](supported-payments.md)._

## capture.php

When the preparation is done a user is redirect to `capture.php`. Here's an example of this file. You can just copy\past the code. 
It has to work for all payments without any modification from your side. 

```php
<?php
//capture.php

use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $registry->getPayment($token->getPaymentName());

if ($reply = $payment->execute(new Capture($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        die();
    }

    throw new \LogicException('Unsupported reply', null, $reply);
}

$requestVerifier->invalidate($token);

header("Location: ".$token->getAfterUrl());
```

_**Note**: Find out more about capture script in the [dedicated chapter](capture-script.md)._

## dome.php

After the capture did its job you will be redirected to [done.php](done-script.md).
The [capture.php](capture-script.md) script always redirects you to `done.php` no matter the payment was a success or not.
In `done.php` we may check the payment status, update the model, dispatch events and so on.

```php
<?php
// done.php

use Payum\Core\Request\GetHumanStatus;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
// $requestVerifier->invalidate($token);

$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new GetHumanStatus($token));

header('Content-Type: application/json');
echo json_encode(array(
    'status' => $status->getValue(),
    'details' => iterator_to_array($status->getModel())
)));
```

_**Note**: Find out more about capture script in the [dedicated chapter](done-script.md)._

## Next 

* [The architecture](the-architecture.md).
* [Supported payments](supported-payments.md).
* [Storages](storages.md).
* [Capture script](capture-script.md).
* [Authorize script](authorize-script.md).
* [Done script](done-script.md).

Back to [index](index.md).
