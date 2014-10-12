# Refund payment

Let's say you already purchased an order and now you want to refund it. 

```php
<?php

use Payum\Core\Request\Refund;
use Payum\Core\Request\GetHumanStatus;

$payment = $this->get('payum')->getPayment('offline');

$payment->execute(new Refund($order));
$payment->execute($status = new GetHumanStatus($order));

if ($status->isRefunded()) {
    // Refund went well
} else {
    // Something went wrong. Lets check details to find out why
     
    var_dump($order->getDetails());
}
```