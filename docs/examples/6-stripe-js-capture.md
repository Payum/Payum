# 3. Stripe.Js. Capture. 

```php
<?php

use Payum\Core\Model\Payment;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;

$model = new Payment();
$model->setCurrencyCode('USD');
$model->setTotalAmount(1);

/** @var GatewayInterface $gateway */
$gateway->execute(new Capture($model));

// or using raw format
 
$model = [
    'amount' => 100,
    'currency' => 'USD',
];

$gateway->execute(new Capture($model);
```

Back to [examples](index.md).
Back to [index](../index.md).
