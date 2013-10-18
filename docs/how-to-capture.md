# How to capture?

```php
<?php
use Buzz\Client\Curl;

use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\SoapClientFactory;
use Payum\Payex\PaymentFactory;
use Payum\Request\CaptureRequest;

$payment = PaymentFactory::create(new OrderApi(new SoapClientFactory(), array(
   'accountNumber' => 'foo',
   'encryptionKey' => 'bar',
   'sandbox' => true
)));

$payment->execute($captureRequest = new CaptureRequest(array(
    'price' => 10000 //10 EUR
    'currency' => 'EUR',
    'vat' => 0,
    'orderId' => 123,
    'productNumber' => 123,
    'purchaseOperation' => OrderApi::PURCHASEOPERATION_AUTHORIZATION,
    'view' => OrderApi::VIEW_CREDITCARD,
    'description' => 'a desc',
    'clientIPAddress' => '127.0.0.1',
    'clientLanguage' => 'en-US'
    'clientIdentifier' => '',
    'additionalValues' => '',
    'priceArgList' => '',
    'agreementRef' => '',
)));
```