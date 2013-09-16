# Get it started.

In this chapter we would talk about payum configuration using only php (not frameworks).
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

_**Note**: You may notice that `$payments` and `$storages` vars are empty? We come back to them when we talk about a real payment gateway._

_**Note**: Consider using something other then `FilesystemStorage` in production. `DoctrineStorage` could be a good alternative._

Let's move on and see how the `capture.php` could look like? We would reuse it for all our payments.

```php
<?php
//capture.php

use Payum\Request\BinaryMaskStatusRequest;
use Payum\Request\SecuredCaptureRequest;
use Payum\Request\RedirectUrlInteractiveRequest;

include 'config.php';

$token = $requestVerifier->verify($_REQUEST);
$payment = $registry->getPayment($token->getPaymentName());

$payment->execute($status = new BinaryMaskStatusRequest($token);)) {
if (false == $status->isNew()) {
    header('HTTP/1.1 400 Bad Request', true, 400);
    exit;
}

if ($interactiveRequest = $payment->execute(new SecuredCaptureRequest($token), true)) {
    if ($interactiveRequest instanceof RedirectUrlInteractiveRequest) {
        header("Location: ".$interactiveRequest->getUrl());
        die();
    }

    throw $interactiveRequest;
}

$this->getHttpRequestVerifier()->invalidate($token);

header("Location: ".$token->getAfterUrl()f);
die();
```

Back to [index](index.md).
