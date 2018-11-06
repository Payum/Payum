<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Payum Bundle. Refund payment

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

* [Back to index](../index.md).