### How to capture?

```php
<?php
//Source: Payum\Examples\ReadmeTest::bigPicture()
use Payum\Examples\Action\CaptureAction;
use Payum\Examples\Action\StatusAction;
use Payum\Request\CaptureRequest;
use Payum\Payment;

//Populate payment with actions.
$payment = new Payment;
$payment->addAction(new CaptureAction());

//Create request and model. It could be anything supported by an action.
$captureRequest = new CaptureRequest(array(
    'amount' => 10,
    'currency' => 'EUR'
));

//Execute request
$payment->execute($captureRequest);

echo 'We are done!';
echo 'We are done!';
```

Back to [index](index.md).