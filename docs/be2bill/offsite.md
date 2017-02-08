# Be2Bill. Offsite

In this chapter we are going to talk about the most common task: purchase of a product using [be2bill](http://www.be2bill.com/).
We assume you already read basic [get it started](../get-it-started.md).
Here we just show you modifications you have to put to the files shown there.

## Installation

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require payum/be2bill php-http/guzzle6-adapter
```

## config.php

Use this when you need a redirect to be2bill site.
We have to only add the gateway factory. All the rest remain the same:

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()

    ->addGateway('be2bill_offsite', [
        'factory' => 'be2bill_offsite',
        'identifier' => 'REPLACE WITH YOURS',
        'password' => 'REPLACE WITH YOURS',
        'sandbox' => true,
    ])

    ->getPayum()
;
```

You have to also go to be2bill admin panel and configure return and notification urls.
The urls you can generate with these code:

```php
<?php
//generate_urls.php

include __DIR__.'/config.php';

use Payum\Core\Payum;

/** @var Payum $payum */

$captureToken = $payum->getTokenFactory()->createCaptureToken(
    'be2bill_offsite', 
    null, 
    ['noinvalidate' => 1], 
    'done_url'
);

echo 'Capture url: ', $captureToken->getTargetUrl(), PHP_EOL;

$notifyToken = $payum->getTokenFactory()->createNotifyToken('be2bill_offsite', null);
echo 'Notify url: ', $notifyToken->getTargetUrl(), PHP_EOL;
```

_**Note**: This step could be skipped if you are not using be2bill offsite._

## prepare.php

Here you have to modify a `gatewayName` value. Set it to `be2bill` or `be2bill_offsite`. The rest remain the same as described in basic [get it started](../get-it-started.md) documentation.


Back to [index](../index.md).