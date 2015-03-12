# Cancel recurring payment.

In the chapter [recurring payments basics](recurring-payments-basics.md) we showed how to configure create a recurring.
Here we show you how to cancel a recurring payment. 

```php
<?php
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Core\Request\GetHumanStatus;


$payment->execute(new Cancel($recurringPayment));
$payment->execute(new Sync($recurringPayment));

$payment->execute($status = new GetHumanStatus($recurringPayment));

if ($status->isCanceled()) {
    // yes it is cancelled
} else {
    // hm... not yet
}
```


Back to [index](index.md).
