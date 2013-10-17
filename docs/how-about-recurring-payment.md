# How about recurring payment?

First you have to create billing agreement and capture it as described [above](#how-to-capture).

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::createBillingAgrement()
use Payum\Request\CaptureRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

$captureBillingAgreement = new CaptureRequest(array(
    'PAYMENTREQUEST_0_AMT' => 0,
    'RETURNURL' => 'http://foo.com/finishPayment',
    'CANCELURL' => 'http://foo.com/finishPayment',
    'L_BILLINGTYPE0' => Api::BILLINGTYPE_RECURRING_PAYMENTS,
    'L_BILLINGAGREEMENTDESCRIPTION0' => 'Subsribe for weather forecast',
));

// ...
```

After you are done with capture, [check billing agreement status](#was-the-payment-finished-successfully). If it has success status create recurring payment:

```php
<?php
//Source: Payum\Paypal\ExpressCheckout\Nvp\Examples\ReadmeTest::createRecurringPaymnt()
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfileRequest;
use Payum\Request\SyncRequest;

$billingAgreementDetails = $captureBillingAgreement->getModel();

$recurringPaymentDetails = new \ArrayObject(array(
    'TOKEN' => $billingAgreementDetails['TOKEN'],
    'PROFILESTARTDATE' => date(DATE_ATOM),
    'DESC' => $billingAgreementDetails['L_BILLINGAGREEMENTDESCRIPTION0'],
    'AMT' => 1.45,
    'CURRENCYCODE' => 'USD',
    'BILLINGPERIOD' => Api::BILLINGPERIOD_DAY,
    'BILLINGFREQUENCY' => 2,
));

$payment->execute(
    new CreateRecurringPaymentProfileRequest($recurringPaymentDetails)
);
$payment->execute(new SyncRequest($recurringPaymentDetails));

$recurringPaymentStatus = new BinaryMaskStatusRequest($recurringPaymentDetails);
$payment->execute($recurringPaymentStatus);

if ($recurringPaymentStatus->isSuccess()) {
    echo 'We are done';
}

echo "Hmm. We are not. Let's check other possible statuses!";
```
