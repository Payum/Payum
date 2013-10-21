# Recurring payments basics.

In this chapter we describe basic steps you have to follow to setup recurring payments.
We would use weather subscription as example.
Subscription costs 0.05$ per day and would last for 7 days.

## Configuration

To start using recurring payment you have to add one more storage to `config.php`.

```php
<?php
//config.php

$paypalRecurringPaymentDetailsClass = 'Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails';
$storages = array(

    // other storages here

    'paypal' => array(
        $paypalRecurringPaymentDetailsClass => new FilesystemStorage(__DIR__.'/storage', $paypalRecurringPaymentDetailsClass, 'idStorage')
    )
);
```

## Create agreement (prepare.php)

A user has to agree to be charged periodically.
For this we have to create an agreement with him.

```php
<?php
//prepare.php

include 'config.php';

$storage = $registry->getStorageForClass($paypalPaymentDetailsClass, 'paypal');

$agreementDetails = $storage->createModel();
$agreementDetails['PAYMENTREQUEST_0_AMT'] = 0;
$agreementDetails['L_BILLINGTYPE0'] = Api::BILLINGTYPE_RECURRING_PAYMENTS;
$agreementDetails['L_BILLINGAGREEMENTDESCRIPTION0'] = $subscription['description'];
$agreementDetails['NOSHIPPING'] = 1;

$storage->updateModel($agreementDetails);

$createRecurringPaymentToken = $tokenStorage->createModel();
$createRecurringPaymentToken->setPaymentName('paypal');
$createRecurringPaymentToken->setDetails($storage->getIdentificator($paymentDetails));
$createRecurringPaymentToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/create_recurring_payment.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($createRecurringPaymentToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('paypal');
$captureToken->setDetails($storage->getIdentificator($paymentDetails));
$captureToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/capture.php?payum_token='.$captureToken->getHash());
$captureToken->setAfterUrl($createRecurringPaymentToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

$agreementDetails['RETURNURL'] = $captureToken->getTargetUrl();
$agreementDetails['CANCELURL'] = $captureToken->getTargetUrl();
$storage->updateModel($agreementDetails);

header("Location: ".$captureToken->getTargetUrl());
```

## Create recurring payment

After capture does its job and agreement will be created.
We are redirected back to `create_recurring_payment.php` script.
Here we have to check status of agreement and if it is good: create recurring payment.
After all we have to redirect user to some safe page.
The page that shows payment details could be a good starting place.

```php
<?php
// create_recurring_payment.php

use Payum\Request\SyncRequest;
use Payum\Request\BinaryMaskStatusRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfileRequest;

include 'config.php';

$token = $this->getHttpRequestVerifier()->verify($_REQUEST);
$this->getHttpRequestVerifier()->invalidate($token);

$payment = $registry->getPayment($token->getPaymentName());

$agreementStatus = new BinaryMaskStatusRequest($token);
$payment->execute($agreementStatus);

$recurringPaymentStatus = null;
if (false == $agreementStatus->isSuccess()) {
    header('HTTP/1.1 400 Bad Request', true, 400);
    exit;
}

$agreementDetails = $agreementStatus->getModel();

$recurringPaymentStorage = $registry->getStorageForClass($paypalRecurringPaymentDetailsClass, $token->getPaymentName());

$recurringPaymentDetails = $recurringPaymentStorage->createModel();
$recurringPaymentDetails['TOKEN'] = $agreementDetails->getToken();
$recurringPaymentDetails['DESC'] = 'Subscribe to the weather forecast for a week. It is 0.05$ per day.';
$recurringPaymentDetails['EMAIL'] = $agreementDetails->getEmail();
$recurringPaymentDetails['AMT'] = 0.05;
$recurringPaymentDetails['CURRENCYCODE'] = 'USD';
$recurringPaymentDetails['BILLINGFREQUENCY'] = 7;
$recurringPaymentDetails['PROFILESTARTDATE'] = date(DATE_ATOM);
$recurringPaymentDetails['BILLINGPERIOD'] = Api::BILLINGPERIOD_DAY;

$payment->execute(new CreateRecurringPaymentProfileRequest($recurringPaymentDetails));
$payment->execute(new SyncRequest($recurringPaymentDetails));

$doneToken = $tokenStorage->createModel();
$doneToken->setPaymentName('paypal');
$doneToken->setDetails(
    $recurringPaymentStorage->getIdentificator($recurringPaymentDetails)
);
$doneToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/done.php?payum_token='.$captureToken->getHash());
$tokenStorage->updateModel($doneToken);

header("Location: ".$doneToken->getTargetUrl());
```