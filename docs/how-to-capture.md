# How to capture?

```php
<?php
use Omnipay\Common\GatewayFactory;

use Payum\Request\CaptureRequest;
use Payum\Bridge\Omnipay\PaymentFactory;

$payment = PaymentFactory::create(GatewayFactory::create('Dummy'));

$payment->execute($captureRequest = new CaptureRequest(array(
    'amount' => 10,
    'card' => array(
        'number' => '5555556778250000', //end zero so will be accepted
        'cvv' => 123,
        'expiryMonth' => 6,
        'expiryYear' => 16,
        'firstName' => 'foo',
        'lastName' => 'bar',
    )
)));
```

