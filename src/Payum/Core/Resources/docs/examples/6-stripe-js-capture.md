# 3. Stripe.Js. Capture. 

```php
<?php

$model = new \Payum\Model\Payment();
$model->setCurrencyCode('USD');
$model->setTotalAmount(1);

$gateway->execute(new \Payum\Core\Request\Capture($model);

// or using raw format
 
$model = array(
   'amount' => 100,
   'currency' => 'USD',
));

$gateway->execute(new \Payum\Core\Request\Capture($model);
```

Back to [examples](index.md).
Back to [index](https://github.com/Payum/Core/tree/master/Resources/docs/index.md).