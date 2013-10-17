# How to capture?

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doCapture()
use Buzz\Client\Curl;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Request\CaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

$payment = PaymentFactory::create(new Api(new Curl, array(
    'username' => 'a_username',
    'password' => 'a_pasword',
    'signature' => 'a_signature',
    'sandbox' => true
)));

$capture = new CaptureRequest(array(
    'PAYMENTREQUEST_0_AMT' => 10,
    'PAYMENTREQUEST_0_CURRENCY' => 'USD',
    'RETURNURL' => 'http://foo.com/finishPayment',
    'CANCELURL' => 'http://foo.com/finishPayment',
));

if ($interactiveRequest = $payment->execute($capture, $expectsInteractive = true)) {
    //save your models somewhere.
    if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
        header('Location: '.$interactiveRequest->getUrl());
        exit;
    }

    throw $interactiveRequest;
```

You can also capture digital goods:

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::doDigitalGoodsCapture()

$capture = new CaptureRequest(array(

    // ...

    'NOSHIPPING' => Api::NOSHIPPING_NOT_DISPLAY_ADDRESS,
    'REQCONFIRMSHIPPING' => Api::REQCONFIRMSHIPPING_NOT_REQUIRED,
    'L_PAYMENTREQUEST_0_ITEMCATEGORY0' => Api::PAYMENTREQUEST_ITERMCATEGORY_DIGITAL,
    'L_PAYMENTREQUEST_0_NAME0' => 'Awesome e-book',
    'L_PAYMENTREQUEST_0_DESC0' => 'Great stories of America.',
    'L_PAYMENTREQUEST_0_AMT0' => 10,
    'L_PAYMENTREQUEST_0_QTY0' => 1,
    'L_PAYMENTREQUEST_0_TAXAMT0' => 2,
));
```
