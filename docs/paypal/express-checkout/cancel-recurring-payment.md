# Paypal Express Checkout. Cancel recurring payment.

In the chapter [recurring payments basics](recurring-payments-basics.md) we showed how to configure create a recurring.
Here we show you how to cancel a recurring payment. 

```php
<?php
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Core\Request\GetHumanStatus;

/** @var \ArrayObject $recurringPayment */

/** @var \Payum\Core\GatewayInterface $gateway */
$gateway->execute(new Cancel($recurringPayment));
$gateway->execute(new Sync($recurringPayment));

$gateway->execute($status = new GetHumanStatus($recurringPayment));

if ($status->isCanceled()) {
    // yes it is cancelled
} else {
    // hm... not yet. check other status isFailed and so on
}
```


Back to [index](../../index.md).
