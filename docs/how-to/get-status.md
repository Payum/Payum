# How to get status

```php
<?php
//Source: Payum\Examples\ReadmeTest::gettingRequestStatus()
use Payum\Examples\Action\StatusAction;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Payment;

//Populate payment with actions.
$payment = new Payment;
$payment->addAction(new StatusAction());

$statusRequest = new BinaryMaskStatusRequest($model);
$payment->execute($statusRequest);

//Or there is a status which require our attention.
if ($statusRequest->isSuccess()) {
    echo 'We are done!';
}

echo 'Uhh something wrong. Check other possible statuses!';
echo 'Uhh something wrong. Check other possible statuses!';
```

Back to [index](../index.md).