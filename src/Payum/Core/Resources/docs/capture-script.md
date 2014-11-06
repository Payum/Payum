# Capture script.

## Simple use case

To perform a capture you just have to do:

```php
<?php
use Payum\Core\Request\Capture;

$payment->execute(new Capture($order));

// or

$payment->execute(new Capture($details));
```

_**Note**: If you've got the "RequestNotSupported" it either means Payum or a gateway do not support the capture._

## Advanced (Secure) use case

To use that you have to configure token factory and create a capture script:

```php
<?php
$token = $tokenFactory->createCaptureToken($paymentName, $details, 'afterCaptureUrl');

header("Location: ".$token->getTargetUrl());
```

This is the script which does all the job related to capturing payments. 
It may show a credit card form, an iframe or redirect a user to payment side. 
The action provides some basic security features. 
Each capture url is completely unique for each purchase, and once we done the url is invalidated.
After a user will be redirected to after url, in our case it will be `done.php` script. 
Here's an example of [done.php](done-script.md) script:

```php
<?php
//capture.php

use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $payum->getPayment($token->getPaymentName());

if ($reply = $payment->execute(new Capture($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
        die();
    }

    throw new \LogicException('Unsupported reply', null, $reply);
}

$requestVerifier->invalidate($token);

header("Location: ".$token->getAfterUrl());
```

_**Note**: If you've got the "Unsupported reply" you have to add an if condition for that. There we have to convert the reply to http response._

Back to [index](index.md).
