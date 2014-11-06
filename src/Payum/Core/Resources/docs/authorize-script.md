# Authorize script.

## Simple use case

To perform a authorize you just have to do:

```php
<?php
use Payum\Core\Request\Authorize;

$payment->execute(new Authorize($order));

// or

$payment->execute(new Authorize($details));
```

_**Note**: If you've got the "RequestNotSupported" it either means Payum or a gateway do not support the authorize._

## Advanced (Secure) use case

To use that you have to configure token factory and create a capture script:

```php
<?php
$token = $tokenFactory->createAuthorizeToken($paymentName, $details, 'afterAuthorizeUrl');

header("Location: ".$token->getTargetUrl());
```

This is the script which does all the job related to payments authorization. 
It may show a credit card form, an iframe or redirect a user to payment side. 
The action provides some basic security features. It is completely unique for each payment, and once we done the url invalidated.
Once we are done here you will be redirected to after capture script. Here's an example [done.php](done-script.md) script.

```php
<?php
//capture.php

use Payum\Core\Request\Authorize;
use Payum\Core\Request\Http\RedirectUrlInteractiveRequest;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $payum->getPayment($token->getPaymentName());

if ($interactiveRequest = $payment->execute(new Authorize($token), true)) {
    if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
        header("Location: ".$interactiveRequest->getUrl());
        die();
    }

    throw new \LogicException('Unsupported interactive request', null, $interactiveRequest);
}

$requestVerifier->invalidate($token);

header("Location: ".$token->getAfterUrl());
```

_**Note**: If you've got the "Unsupported interactive request" you have to add an if condition for that. There we have to convert the request to http response._

Back to [index](index.md).
