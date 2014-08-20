# Get it started.

Here we describe basic steps required by all supported payments. We are going to setup models, storages, a security layer and so on.
All that stuff will be used later.

## Install

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/xxx:*@stable"
```

_**Note**: Where payum/xxx is a payum package, for example it could be payum/paypal-express-checkout-nvp. Look at [supported payments](supported-payments.md) to find out what you can use._

_**Note**: Use payum/payum if you want to install all payments at once._

## Configure

Before we configure the payum let's look at the flow diagram.
This flow is same all payments so once you familiar with it any other payments could be added easily.

![How payum works](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgcGF5cGFsLmNvbQoACwxVc2VyAAQNcHJlcGFyZS5waHAAHA1jYXB0dQAFE2RvbgAnBgpVc2VyLT4ANQs6AEUIIGEgcGF5bWVudAoAVAstLT4rAEsLOgBbCCB0b2tlbgoKAGcLLS0-AIE2CjogcmVxdWVzdCBhdXRoZW50aWNhdGlvbgoAgVkKLS0-AE0NZ2l2ZSBjb250cm9sIGJhY2sATg8tAIE-CDoAgUsFAHsHAIFTCC0-VXNlcjogc2hvdwCBQQggcmVzdWx0Cg&s=default)

Now configuration. Let's start from defining some models.
First one is a `PaymentDetails`.
It will storage all the information related to the payment:

```php
<?php
namespace App\Model;

use Payum\Core\Model\ArrayObject;

class PaymentDetails extends ArrayObject
{
    protected $id;
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

use Payum\Core\Registry\SimpleRegistry;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Security\PlainHttpRequestVerifier;
use Payum\Core\Security\GenericTokenFactory;

$tokenStorage = new FilesystemStorage('/path/to/storage', 'App\Model\PaymentSecurityToken', 'hash');
$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

$detailsClass = 'App\Model\PaymentDetails';

$storages = array(
    $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass, 'id')
);

$payments = array(
    // here we will put a payment object.
);

$registry = new SimpleRegistry($payments, $storages);

$tokenFactory = new GenericTokenFactory(
    $tokenStorage,
    $registry,
    'http://'.$_SERVER['HTTP_HOST'],
    'capture.php',
    'notify.php',
    'authorize.php'
);
```

An initial configuration for Payum basically wants to ensure we have things ready to be stored such as
a token, or a payment details. We also would like to have a registry of various payments supported and the place where they can store their information (e.g. payment details).

_**Note**: Consider using something other than `FilesystemStorage` in production. `DoctrineStorage` may be a good alternative._

## Prepare

At this stage we have to create a payment details model. Put some details into it. 
Create a capture token for it and delegate the job to [`capture.php`](capture-script.md) script.
Here's just an example of how it may look like, or choose a [supported payment](supported-payments.md) and look at it working example

```php
<?php
// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$details = $storage->createModel();
$details['cur'] = 'EUR';
$details['amt'] = 1.23;
$storage->updateModel($details);

$captureToken = $tokenFactory->createCaptureToken('aPaymentName', $details, 'done.php');

header("Location: ".$captureToken->getTargetUrl());
```

## Capture

If you read the previous chapter carefully you may noticed that in the `prepare.php` script we set
[`capture.php`](capture-script.md) as the target url of capture token.
On the last lines of `prepare.php` we delegated the job to it.

The capture script has to be reused by all supported payment. So you can just [copy\paste](capture-script.md) it.

## When we are done

After the capture did its job you will be redirected to [`done.php`](done-script.md).
The [`capture.php`](capture-script.md) script always redirects you to `done.php` no matter the payment was a success or not.
In `done.php` we may check the payment status, update the model, dispatch events and so on.

## Next 

* [The architecture](the-architecture.md).
* [Supported payments](supported-payments.md).
* [Capture script](capture-script.md).
* [Done script](done-script.md).

Back to [index](index.md).
