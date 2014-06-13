# Onsite payment example.

Onsite means a user is redirected to be2bill site. There they enter credit card details, do purchase and come back to us.

## Configuration

First we have modify `config.php` a bit.
We need to add payment factory and payment details storage.

```php
<?php

use Payum\Be2Bill\Api as Be2BillApi;
use Payum\Be2Bill\OnsitePaymentFactory as Be2BillOnsitePaymentFactory;

//config.php

// ...

$storages = array(
    'be2bill_onsite' => array(
        $detailsClass => new FilesystemStorage('/path/to/storage', $detailsClass)
    )
);

$payments = array(
    'be2bill_onsite' => Be2BillOnsitePaymentFactory::create(new Be2BillApi(new Curl, array(
       'identifier' => 'REPLACE WITH YOURS',
       'password' => 'REPLACE WITH YOURS',
       'sandbox' => true
    )
);
```

## Prepare payment

```php
<?php

use Payum\Core\Security\SensitiveValue;

// prepare.php

include 'config.php';

$storage = $registry->getStorage($detailsClass);

$paymentDetails = $storage->createModel();
$paymentDetails['AMOUNT'] = 100; //1$
$paymentDetails['CLIENTEMAIL'] = 'buyer@example.com';
$paymentDetails['CLIENTUSERAGENT'] = 'Firefox';
$paymentDetails['CLIENTIP'] = 192.168.0.1;
$paymentDetails['CLIENTIDENT'] = 'payerId';
$paymentDetails['DESCRIPTION'] = 'Payment for digital stuff';
$paymentDetails['ORDERID'] = 'orderId';
$storage->updateModel($paymentDetails);

$captureToken = $tokenFactory->createCaptureToken('be2bill_onsite', $paymentDetails, 'done.php');

$_REQUEST['payum_token'] = $captureToken;

include 'capture.php';
```

That's it. As you see we configured be2bill payment in `config.php` and set details in `prepare.php`.
The `capture.php` and `done.php` scripts remain same.

Back to [index](index.md).