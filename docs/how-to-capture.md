# How to capture?

```php
<?php
use Buzz\Client\Curl;

use Payum\Be2Bill\Api;
use Payum\Be2Bill\PaymentFactory;
use Payum\Request\CaptureRequest;

$payment = PaymentFactory::create(new Api(new Curl(), array(
   'identifier' => 'foo',
   'password' => 'bar',
   'sandbox' => true
)));

$payment->execute($captureRequest = new CaptureRequest(array(
    'AMOUNT' => '1000', // 10$
    'CLIENTUSERAGENT' => 'Firefox',
    'CLIENTIP' => '82.117.234.33',
    'CLIENTIDENT' => 'anIdent',
    'CLIENTEMAIL' => 'test@example.com',
    'CARDCODE' => '4111111111111111',
    'DESCRIPTION' => 'aDescr',
    'ORDERID' => 'anId',
    'CARDFULLNAME' => 'John Doe',
    'CARDVALIDITYDATE' => '10-13',
    'CARDCVV' => '123'
)));
```