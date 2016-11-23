# Cancel recurring payment.

In the chapter [recurring payments basics](recurring-payments-basics.md) we showed how to configure create a recurring.
Here we show you how to cancel a recurring payment. 

```php
<?php
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Core\Request\GetHumanStatus;


$gateway->execute(new Cancel($recurringPayment));
$gateway->execute(new Sync($recurringPayment));

$gateway->execute($status = new GetHumanStatus($recurringPayment));

if ($status->isCanceled()) {
    // yes it is cancelled
} else {
    // hm... not yet
}
```


Back to [index](index.md).
