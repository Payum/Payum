# Recurring payments basics.

In this chapter we describe basic steps you have to follow to setup recurring payments.
We would use weather subscription as example.
Subscription costs 0.05$ per day and would last for 7 days.

## Configuration

Recurring payments require two additional models.
First one would contain agreement details and the second one recurring payment details.
Let's define them:

```php
<?php
namespace App\Model;

use Payum\Core\Model\ArrayObject;

class AgreementDetails extends \ArrayObject
{
}
```

And recurring payment details model:


```php
<?php
namespace App\Model;

use Payum\Core\Model\ArrayObject;

class RecurringPaymentDetails extends \ArrayObject
{
}
```

Now we have to adjust `config.php` to support paypal recurring payments:

```php
<?php
//config.php

$agreementDetailsClass = 'App\Model\AgreementDetails';
$recurringPaymentDetailsClass = 'App\Model\RecurringPaymentDetails';

$storages[$agreementDetailsClass] = new FilesystemStorage(
    __DIR__.'/storage',
    $agreementDetailsClass
);
$storages[$recurringPaymentDetailsClass] = new FilesystemStorage(
    __DIR__.'/storage',
    $recurringPaymentDetailsClass
);
```

## Establish agreement (prepare.php)

A user has to agree to be charged periodically.
For this we have to create an agreement with him.

```php
<?php
//prepare.php

include 'config.php';

use Payum\Paypal\ExpressCheckout\Nvp\Api;

$storage = $payum->getStorage($agreementDetailsClass);

$agreementDetails = $storage->create();
$agreementDetails['PAYMENTREQUEST_0_AMT'] = 0;
$agreementDetails['L_BILLINGTYPE0'] = Api::BILLINGTYPE_RECURRING_PAYMENTS;
$agreementDetails['L_BILLINGAGREEMENTDESCRIPTION0'] = "Insert some description here";
$agreementDetails['NOSHIPPING'] = 1;
$storage->update($agreementDetails);

$captureToken = $tokenFactory->createCaptureToken('paypal', $agreementDetails, 'create_recurring_payment.php');

$storage->update($agreementDetails);

header("Location: ".$captureToken->getTargetUrl());
```

The script is pretty similar to ordinary purchase.
The only difference here we set some special options to agreementDetails.
The rest is same. Create capture token.
Done token in this example renamed to `createRecurringPaymentToken`.
This is because we have one more step to do before we can go to `done.php`.

## Create recurring payment

After capture did its job and agreement is created.
We are redirected back to `create_recurring_payment.php` script.
Here we have to check status of agreement and if it is good: create recurring payment.
After all we have to redirect user to some safe page.
The page that shows payment details could be a good starting place.

```php
<?php
// create_recurring_payment.php

use Payum\Core\Request\Sync;
use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfile;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$requestVerifier->invalidate($token);

$payment = $payum->getPayment($token->getPaymentName());

$agreementStatus = new GetHumanStatus($token);
$payment->execute($agreementStatus);

if (!$agreementStatus->isCaptured()) {
    header('HTTP/1.1 400 Bad Request', true, 400);
    exit;
}

$agreementDetails = $agreementStatus->getModel();

$storage = $payum->getStorage($recurringPaymentDetailsClass);

$recurringPaymentDetails = $storage->create();
$recurringPaymentDetails['TOKEN'] = $agreementDetails['TOKEN'];
$recurringPaymentDetails['DESC'] = 'Subscribe to weather forecast for a week. It is 0.05$ per day.';
$recurringPaymentDetails['EMAIL'] = $agreementDetails['EMAIL'];
$recurringPaymentDetails['AMT'] = 0.05;
$recurringPaymentDetails['CURRENCYCODE'] = 'USD';
$recurringPaymentDetails['BILLINGFREQUENCY'] = 7;
$recurringPaymentDetails['PROFILESTARTDATE'] = date(DATE_ATOM);
$recurringPaymentDetails['BILLINGPERIOD'] = Api::BILLINGPERIOD_DAY;

$payment->execute(new CreateRecurringPaymentProfile($recurringPaymentDetails));
$payment->execute(new Sync($recurringPaymentDetails));

$doneToken = $tokenFactory->createToken('paypal', $recurringPaymentDetails, 'done.php');

header("Location: ".$doneToken->getTargetUrl());
```

Back to [index](index.md).
