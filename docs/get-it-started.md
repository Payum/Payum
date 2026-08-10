# Get started

Here we describe basic steps required by all supported gateways. We are going to setup models, storages, a security layer and so on. All that stuff will be used later.

{% hint style="info" %}
_**Note**: This page sets up the models, storages and security layer every gateway needs, which is unchanged. How a gateway itself is written changed in 2.0 — see the_ [_Gateways_](gateways/README.md) _chapter._
{% endhint %}

{% hint style="info" %}
_**Note**: If you are working with **Symfony framework** look read the bundle's_ [_documentation_](./#symfony-payum-bundle) _instead._
{% endhint %}

{% hint style="info" %}
_**Note**: If you are working with **Laravel framework** look read the_ [_documentation_](./#laravel-payum-package) _instead._
{% endhint %}

### Install

The preferred way to install the library is using [composer](http://getcomposer.org/). Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/offline php-http/guzzle7-adapter
```

{% hint style="info" %}
_**Note**: Where **payum/offline** is a php payum extension, you can for example change it to **payum/paypal-express-checkout-nvp** or **payum/stripe**. Look at_ [_supported gateways_](supported-gateways.md) _to find out what you can use._
{% endhint %}

{% hint style="info" %}
_**Note**: Use **payum/payum** if you want to install all gateways at once._
{% endhint %}

{% hint style="info" %}
_**Note**: Use **php-http/guzzle7-adapter** is just an example. You can use any of_ [_these adapters_](https://packagist.org/providers/php-http/client-implementation)_._
{% endhint %}

Before we configure payum, let's look at the flow diagram. This flow is same for all gateways so once you familiar with it any other gateways could be added easily.

![How payum works](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgcGF5cGFsLmNvbQoACwxVc2VyAAQNcHJlcGFyZS5waHAAHA1jYXB0dQAFE2RvbgAnBgpVc2VyLT4ANQs6AEUIIGEgcGF5bWVudAoAVAstLT4rAEsLOgBbCCB0b2tlbgoKAGcLLS0-AIE2CjogcmVxdWVzdCBhdXRoZW50aWNhdGlvbgoAgVkKLS0-AE0NZ2l2ZSBjb250cm9sIGJhY2sATg8tAIE-CDoAgUsFAHsHAIFTCC0-VXNlcjogc2hvdwCBQQggcmVzdWx0Cg\&s=default)

As you can see we have to create some php files: `config.php`, `prepare.php`, `capture.php` and `done.php`. At the end you will have the complete solution and it would be [much easier to add](paypal/express-checkout/get-it-started.md) other gateways. Let's start from the `config.php` and continue with rest after:

### config.php

Here we can put our gateways, storages. Also we can configure security components. The `config.php` has to be included to all left files.

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addGateway('aGateway', [
        'factory' => 'offline',
    ])

    ->getPayum()
;
```

_**Note**: There are other_ [_storages_](storages.md) _available. Such as Doctrine ORM\MongoODM._

_**Note**: Consider using something other than `FilesystemStorage` in production._

{% hint style="info" %}
_**Note**: `PayumBuilder` configures a_ [_dependency injection container_](di/README.md) _for you. To share a service such as a logger or your own HTTP client with every gateway, or to replace one of Payum's defaults, see_ [_Dependency Injection_](di/getting-started.md)_._
{% endhint %}

### prepare.php

At this stage we have to create an order and add some information into it. `prepare()` then stores it and gives you back a capture token, which delegates the job to the [capture.php](examples/capture-script.md) script. Here's an offline gateway example:

```php
<?php
// prepare.php

use Payum\Core\Model\Payment;

include __DIR__.'/config.php';

$gatewayName = 'aGateway';

$payment = new Payment();
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

/** @var \Payum\Core\Payum $payum */
$captureToken = $payum->prepare($gatewayName, $payment);

header("Location: ".$captureToken->getTargetUrl());
```

`prepare()` finds the storage registered for the model you give it, persists the model, and
builds the capture token. It works with any model that has a storage: a `Payment`, an
`ArrayObject` of raw gateway details, or [your own order class](your-order-integration.md).

Once captured, the payer is sent to `done.php`. Pass a third argument to send them elsewhere:

```php
$captureToken = $payum->prepare($gatewayName, $payment, 'thanks.php');
```

To change it for every payment instead, set it once on the builder:

```php
$payum = (new \Payum\Core\PayumBuilder())
    ->setGenericTokenFactoryPaths(['done' => 'thanks.php'])
    // ...
    ->getPayum()
;
```

{% hint style="info" %}
_**Note**: doing this by hand still works. `prepare()` is shorthand for `getStorage()`, `update()`
and_ [_`getTokenFactory()->createCaptureToken()`_](examples/capture-script.md)_, which you can call
yourself when you need more control._
{% endhint %}

_**Note**: There are examples for all_ [_supported gateways_](supported-gateways.md)_._

### capture.php

When the preparation is done a user is redirect to `capture.php`. Here's an example of this file. You can just copy\past the code. It has to work for all gateways without any modification from your side.

```php
<?php
//capture.php

use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpPostRedirect;

include __DIR__.'/config.php';

/** @var \Payum\Core\Payum $payum */
$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

/** @var \Payum\Core\GatewayInterface $gateway */
if ($reply = $gateway->execute(new Capture($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        die();
    } elseif ($reply instanceof HttpPostRedirect) {
        echo $reply->getContent();
        die();
    }

    throw new \LogicException('Unsupported reply', null, $reply);
}

/** @var \Payum\Core\Payum $payum */
$payum->getHttpRequestVerifier()->invalidate($token);

header("Location: ".$token->getAfterUrl());
```

_**Note**: Find out more about capture script in the_ [_dedicated chapter_](examples/capture-script.md)_._

### done.php

After the capture did its job you will be redirected to [done.php](examples/done-script.md). The [capture.php](examples/capture-script.md) script always redirects you to `done.php` no matter the payment was a success or not. In `done.php` we may check the payment status, update the model, dispatch events and so on.

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

_**Note**: Find out more about done script in the_ [_dedicated chapter_](examples/done-script.md)_._

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/Payum)
