# Get it started.

In this chapter we are going to talk about the most common task: purchasing a product.
We assume you already read [payum's get it started documentation](https://github.com/Payum/Payum/blob/master/docs/get-it-started.md).

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/payum:0.6.*@dev" "payum/paypal-rest:0.6.*@dev"
```

_**Note**: It is advised to use stable versions. Visit [packagist](https://packagist.org/packages/payum/) to find out more about versions available._

Now you have all required code downloaded, autoload configured.


## Configuration

First we have modify `config.php` a bit.
We have to configure `paypal/rest-api-sdk`.
Then add paypal rest payment to registry.
At last setup a model storage.

```php
<?php
//config.php

// You way want to modify it to suite your needs
$paypalRestPaymentDetailsClass = 'Payum\Paypal\Rest\Model\PaymentDetails';
$storages = array(

    // other storages here

    'paypalRest' => array(
        $paypalRestPaymentDetailsClass => new FilesystemStorage(__DIR__.'/storage', $paypalRestPaymentDetailsClass, 'idStorage')
    )
);

define("PP_CONFIG_PATH", __DIR__);

$configManager = \PPConfigManager::getInstance();

$cred = new OAuthTokenCredential(
    $configManager->get('acct1.ClientId'),
    $configManager->get('acct1.ClientSecret')
);

$payments = array(

    // other storages here

    'paypalRest' => RestPaymentFactory::create(new ApiContext($cred, 'Request' . time()))
);

$payments['paypalRest']->addExtension(new StorageExtension($storages['paypalRest'][$paypalRestPaymentDetailsClass]));
```

## Prepare payment

```php
<?php
// prepare.php

include 'config.php';

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;

$storage = $registry->getStorage($paypalRestPaymentDetailsClass);

$paymentDetails = $storage->createModel();
$storage->updateModel($paymentDetails);

$payer = new Payer();
$payer->payment_method = "paypal";

$amount = new Amount();
$amount->currency = "USD";
$amount->total = "1.00";

$transaction = new Transaction();
$transaction->amount = $amount;
$transaction->description = "This is the payment description.";

$captureToken = $tokenFactory->createCaptureToken('paypalRest', $paymentDetails, 'create_recurring_payment.php');

$redirectUrls = new RedirectUrls();
$redirectUrls->return_url = $captureToken->getTargetUrl();
$redirectUrls->cancel_url = $captureToken->getTargetUrl();

$paymentDetails->intent = "sale";
$paymentDetails->payer = $payer;
$paymentDetails->redirect_urls = $redirectUrls;
$paymentDetails->transactions = array($transaction);

$storage->updateModel($paymentDetails);

header("Location: ".$captureToken->getTargetUrl());
```

That's it. As you see we have to update `config.php` and create `prepare.php`.
Capture and Done scripts remain same for all payments.

Back to [index](index.md).