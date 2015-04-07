# Refund payment

Let's say you already purchased an order and now you want to refund it. 

```php
<?php

use Payum\Core\Request\Refund;
use Payum\Core\Request\GetHumanStatus;

$gateway = $this->get('payum')->getGateway('offline');

$gateway->execute(new Refund($payment));
$gateway->execute($status = new GetHumanStatus($payment));

if ($status->isRefunded()) {
    // Refund went well
} else {
    // Something went wrong. Lets check details to find out why
     
    var_dump($payment->getDetails());
}
```