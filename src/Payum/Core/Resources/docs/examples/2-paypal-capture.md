# 2. Paypal. Redirects. 

```php
<?php

$model = new \Payum\Model\Order();
$model->setCurrencyCode('USD');
$model->setTotalAmount(1);
$model->setDetails(array(
    'RETURNURL' => 'http://return.url',
    'CANCELURL' => 'http://cancel.url',
));

$payment->execute(new \Payum\Core\Request\Capture($model);

// or using raw format
 
$model = array(
   'PAYMENTREQUEST_0_CURRENCYCODE' => 'USD',
   'PAYMENTREQUEST_0_AMT' => 1,
   'RETURNURL' => 'http://return.url',
   'CANCELURL' => 'http://cancel.url',
));

$payment->execute(new \Payum\Core\Request\Capture($model);
```