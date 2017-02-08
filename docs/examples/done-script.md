## Done script

This is the most important script for you, because here you have to decide what to do next.
Was the payment successful, or not?
What to do in each case?
You have to put here your own logic, for example on success you may want to send a welcome mail, and increment points on a user account.
Or, You may want notify a delivery company about purchased product, asking for a delivery.
Payum allows you easily get the status, validates the url.

## Getting model

There are two ways to get the model associated with the token:

First one, Let Payum fetch the model for you while executing a request with a token as model.

```php
<?php
// done.php

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Payum;

include __DIR__.'/config.php';

/** @var Payum $payum */

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);

$gateway = $payum->getGateway($token->getGatewayName());
$gateway->execute($status = new GetHumanStatus($token));

$model = $status->getFirstModel());
```

Second one, Get the model from the storage directly.

```php
<?php
// done.php

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Payum;

include __DIR__.'/config.php';

/** @var Payum $payum */

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);

/** @var \Payum\Core\Storage\IdentityInterface $identity **/
$identity = $token->getDetails();
$model = $payum->getStorage($identity->getClass())->find($identity);

$gateway = $payum->getGateway($token->getGatewayName());
$gateway->execute($status = new GetHumanStatus($model));
```

## Payment status

Now, you have a model and status. And you can find out what is the status of a payment.

```php
<?php

/** @var \Payum\Core\Request\GetHumanStatus $status */

// using shortcut
if ($status->isCaptured() || $status->isAuthorized()) {
  // success
}

// using shortcut
if ($status->isPending()) {
  // most likely success, but you have to wait for a push notification.
}

// using shortcut
if ($status->isFailed() || $status->isCanceled()) {
  // the payment has failed or user canceled it.
}
```

## Invalidation

A good practice is to do some actions and redirect a user to another url.
This url should not be accessible more than once.
This way the user is not able to accidentally purchase the same order two times for example.

```php
<?php

/** @var Payum\Core\Payum $payum */
/** @var Payum\Core\Security\TokenInterface $token */

// you can invalidate the token. The url could not be requested any more.
$payum->getHttpRequestVerifier()->invalidate($token);
```

_**Note**: We advice you to invalidate(remove) the token as soon as you do not need it._

Back to [examples](index.md).
Back to [index](../index.md).
