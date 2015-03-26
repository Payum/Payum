# 3. Stripe.Js. Capture. 

```php
<?php

$model = new \Payum\Model\Order();
$model->setCurrencyCode('USD');
$model->setTotalAmount(1);

$payment->execute(new \Payum\Core\Request\Capture($model);

// or using raw format
 
$model = array(
   'amount' => 100,
   'currency' => 'USD',
));

$payment->execute(new \Payum\Core\Request\Capture($model);
```