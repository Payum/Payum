<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# 2. Paypal. Redirects. 

```php
<?php

use Payum\Core\Model\Payment;
use Payum\Core\GatewayInterface;
use Payum\Core\Request\Capture;

$model = new Payment();
$model->setCurrencyCode('USD');
$model->setTotalAmount(1);
$model->setDetails(array(
    'RETURNURL' => 'http://return.url',
    'CANCELURL' => 'http://cancel.url',
));

/** @var GatewayInterface $gateway */
$gateway->execute(new Capture($model));

// or using raw format
 
$model = array(
   'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
   'PAYMENTREQUEST_0_AMT' => 1,
   'RETURNURL' => 'http://return.url',
   'CANCELURL' => 'http://cancel.url',
);

$gateway->execute(new Capture($model);
```

Back to [examples](index.md).
Back to [index](../index.md).