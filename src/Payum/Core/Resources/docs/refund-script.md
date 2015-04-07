# Refund script.

## Simple use case

To perform a refund you just have to do:

```php
<?php
use Payum\Core\Request\Refund;

$gateway->execute(new Refund($order));

// or

$gateway->execute(new Refund($details));
```

_**Note**: If you've got the "RequestNotSupported" it either means Payum or a gateway do not support the refund._

## Advanced (Secure) use case

To use that you have to configure token factory and create a refund script:

```php
<?php
$token = $tokenFactory->createRefundToken($gatewayName, $details, 'afterRefundUrl');

header("Location: ".$token->getTargetUrl());
```

This is the script which does all the job related to capturing payments. 
It may show a credit card form, an iframe or redirect a user to gateway side. 
The action provides some basic security features. 
Each capture url is completely unique for each purchase, and once we done the url is invalidated.
After a user will be redirected to after url, in our case it will be `done.php` script. 
Here's an example of [done.php](done-script.md) script:

```php
<?php
//capture.php

use Payum\Core\Request\Refund;
use Payum\Core\Reply\HttpRedirect;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

if ($reply = $gateway->execute(new Refund($token), true)) {
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

