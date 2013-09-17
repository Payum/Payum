# Get it started.

In this chapter we are going to talk about payum configuration using only php (no frameworks).
This is the minimal code you need to write to get benefits from payum.
All other examples will be based on the `config.php` described here.

```php
<?php
//config.php

use Payum\Registry\SimpleRegistry;
use Payum\Storage\FilesystemStorage;
use Payum\Security\PlainHttpRequestVerifier;

$tokenStorage = new FilesystemStorage('/path/to/storage', 'Payum/Model/Token', 'hash');
$requestVerifier = new PlainHttpRequestVerifier($tokenStorage);

$storages = array();
$payments = array();

$registry = new SimpleRegistry($payments, $storages, null, null);
```

_**Note**: You may notice that `$payments` and `$storages` vars are empty? We will come back to them when we talk about a real payment gateway._

_**Note**: Consider using something other than `FilesystemStorage` in production. `DoctrineStorage` could be a good alternative._

Now I would show you paypal express checkout specific code. You have to modify it in case you use something other then paypal.

```php
<?php
//config.php

use Buzz\Client\Curl;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

$payments = array(
    'paypal_express_checkout' => PaymentFactory::create(new Api(new Curl, array(
        'username' => 'REPLACE WITH YOURS',
        'password' => 'REPLACE WITH YOURS',
        'signature' => 'REPLACE WITH YOURS',
    )))
);

$storages = array(
    'paypal_express_checkout' => array(
        'Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails' => new FilesystemStorage(
            '/path/to/storage',
            'Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails',
            'id'
        )
    )
)
```

First we created payment with factory.
The factory is just shortcut.
Inside it creates a payment object and fill it with paypal actions, and its api.

After we created a storage for `PaymentDetails` model.
This model will hold all information about the payment.

_**Note**: You are not required to use this model. Payum is designed to work with array or ArrayAccess._

Now, it is time to see how we can create a payment:

```php
<?php
// prepare_paypal_payment.php

include 'config.php';

$storage = $registry->getStorageForClass(
    'Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails',
    'paypal_express_checkout'
);

$paymentDetails = $storage->createModel();
$paymentDetails['PAYMENTREQUEST_0_CURRENCYCODE'] = 'EUR';
$paymentDetails['PAYMENTREQUEST_0_AMT'] = 1.23;
$storage->updateModel($paymentDetails);

$captureDoneToken = $tokenStorage->createModel();
$captureDoneToken->setPaymentName('paypal_express_checkout');
$captureDoneToken->setDetails($storage->getIdentifier($paymentDetails));
$captureDoneToken->setTargetUrl('capture_done.php?payum_token'.$captureDoneToken->getHash());
$tokenStorage->updateModel($captureDoneToken);

$captureToken = $tokenStorage->createModel();
$captureToken->setPaymentName('paypal_express_checkout');
$captureToken->setDetails($storage->getIdentifier($paymentDetails));
$captureToken->setTargetUrl('capture.php?payum_token'.$captureToken->getHash());
$captureToken->setAfterUrl($captureDoneToken->getTargetUrl());
$tokenStorage->updateModel($captureToken);

header("Location: ".$captureToken->getTargetUrl());
die();
```

If you read the code carefully you may notice `capture.php` we set as target url of capture token.
We do not show user any sensative information or guessable information. What user will see is the random hash.
All the payment information was related to this token.

Let's move on and see how that `capture.php` file could look like? By the way we are going to reuse it for all our payments.

```php
<?php
//capture.php

use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\SecuredCaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new BinaryMaskStatusRequest($token));
if (false == $status->isNew()) {
    header('HTTP/1.1 400 Bad Request', true, 400);
    exit;
}

if ($interactiveRequest = $payment->execute(new SecuredCaptureRequest($token), true)) {
    if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
        header("Location: ".$interactiveRequest->getUrl());
        die();
    }

    throw \LogicException('Unsupported interactive request', null $interactiveRequest);
}

$requestVerifier->invalidate($token);

header("Location: ".$token->getAfterUrl());
die();
```

The last line of capture request does redirect to `capture_done.php`.
You are always redirected there no matter what payment status.
The good question here: What should you do there?
Well, In most cases you have to check status and do what ever you want, for example print message:

```php
<?php
include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new BinaryMaskStatusRequest($token));
if ($status->isSuccess()) {
    echo 'payment captured successfully';
} else {
    echo 'payment captured not successfully';
}
```

_**Note**: Pay attention to there are other statuses present like pending, suspended, failure, canceled._

Back to [index](index.md).
