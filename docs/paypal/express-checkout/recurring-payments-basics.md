# Paypal Express Checkout. Recurring payments basics.

In this chapter we describe the basic steps you have to follow to set up recurring payments.
We will use weather subscription as example.
Subscription costs $0.05 per day and would last for 7 days.

## Configuration

Recurring payments require two additional models:
First, one would contain agreement details and the second one recurring payment details.
Let's define them:

```php
<?php
namespace App\Model;

class AgreementDetails extends \ArrayObject
{
}
```

And recurring payment details model:


```php
<?php
namespace App\Model;

class RecurringPaymentDetails extends \ArrayObject
{
}
```

Now we have to adjust `config.php` to support paypal recurring payments:

```php
<?php
//config.php

use Payum\Core\Storage\FilesystemStorage;

$agreementClass = 'App\Model\AgreementDetails';
$recurringPaymentClass = 'App\Model\RecurringPaymentDetails';

$storages[$agreementClass] = new FilesystemStorage(
    __DIR__.'/storage',
    $agreementClass
);
$storages[$recurringPaymentClass] = new FilesystemStorage(
    __DIR__.'/storage',
    $recurringPaymentClass
);
```

## Establish agreement (prepare.php)

A user has to agree to be charged periodically.
For this we have to create an agreement with him.

```php
<?php
//prepare.php

include __DIR__.'/config.php';

use Payum\Paypal\ExpressCheckout\Nvp\Api;

/** @var \ArrayObject $agreementClass */

/** @var \Payum\Core\Payum $payum */
$storage = $payum->getStorage($agreementClass);

$agreement = $storage->create();
$agreement['PAYMENTREQUEST_0_AMT'] = 0; // For an initial amount to be charged please add it here, eg $10 setup fee
$agreement['L_BILLINGTYPE0'] = Api::BILLINGTYPE_RECURRING_PAYMENTS;
$agreement['L_BILLINGAGREEMENTDESCRIPTION0'] = "Insert some description here";
$agreement['NOSHIPPING'] = 1;
$storage->update($agreement);

$captureToken = $payum->getTokenFactory()->createCaptureToken('paypal', $agreement, 'create_recurring_payment.php');

$storage->update($agreement);

header("Location: ".$captureToken->getTargetUrl());
```

This script is pretty similar to an ordinary purchase.
The only difference here is that we set some special options to agreementDetails.
The rest is the same. Create capture token.
The 'done' token in this example is renamed to `createRecurringPaymentToken`.
This is because we have one more step to do before we can go to `done.php`.

## Create recurring payment

After capture has done it's job and the agreement has been created we are redirected back to the `create_recurring_payment.php` script.
Here we will check the status of the agreement and if it has been accepted: create a recurring payment.
After everything is complete we should redirect the user to a safe page - the page that shows payment details could be a good starting place.

```php
<?php
// create_recurring_payment.php

use Payum\Core\Request\Sync;
use Payum\Core\Request\GetHumanStatus;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfile;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

include __DIR__.'/config.php';

/** @var \ArrayObject $recurringPaymentClass */

/** @var \Payum\Core\Payum $payum */
$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$payum->getHttpRequestVerifier()->invalidate($token);

$gateway = $payum->getGateway($token->getGatewayName());

$agreementStatus = new GetHumanStatus($token);
$gateway->execute($agreementStatus);

if (!$agreementStatus->isCaptured()) {
    header('HTTP/1.1 400 Bad Request', true, 400);
    exit;
}

$agreement = $agreementStatus->getModel();

$storage = $payum->getStorage($recurringPaymentClass);

$recurringPayment = $storage->create();
$recurringPayment['TOKEN'] = $agreement['TOKEN'];
$recurringPayment['DESC'] = 'Subscribe to weather forecast for a week. It is 0.05$ per day.'; // Desc must match agreement 'L_BILLINGAGREEMENTDESCRIPTION' in prepare.php
$recurringPayment['EMAIL'] = $agreement['EMAIL'];
$recurringPayment['AMT'] = 0.05;
$recurringPayment['CURRENCYCODE'] = 'USD';
$recurringPayment['BILLINGFREQUENCY'] = 7;
$recurringPayment['PROFILESTARTDATE'] = date(DATE_ATOM);
$recurringPayment['BILLINGPERIOD'] = Api::BILLINGPERIOD_DAY;

$gateway->execute(new CreateRecurringPaymentProfile($recurringPayment));
$gateway->execute(new Sync($recurringPayment));

$doneToken = $payum->getTokenFactory()->createToken('paypal', $recurringPayment, 'done.php');

header("Location: " . $doneToken->getTargetUrl());
```

Back to [index](../../index.md).
