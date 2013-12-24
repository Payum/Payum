# Instant payment notification.

A notification is a callback. A payment gateway can send it back to us to let us know about changes.
It could be [Paypal Instant Payment Notification (IPN)](https://developer.paypal.com/webapps/developer/docs/classic/products/instant-payment-notification/) or [Payex Transaction Callback](http://www.payexpim.com/quick-guide/9-transaction-callback/) for example.
Here in this chapter we show you how to store it somewhere and process it later (with a cron script for example).

The diagram shows two examples where notification could be very handy:

![notification](http://www.websequencediagrams.com/cgi-bin/cdraw?lz=cGFydGljaXBhbnQgUGF5cGFsCgAHDGNhcHR1cmUucGhwAAsNbm90aWZ5ABIFCgAZCy0-KwA_BjogYSBwdXJjYWhzZQoAUgYtPi0AQws6IHBlbmRpbmcAFggtPgBKCjogc3VjY2VzcwBiBmljYXRpb24AMTkARgcAVBZjYW5jZWxlZCAodXNlciB2b2lkIG9uIHAAggcFIHNpZGUp&s=default)

## Preparations

First we have to create a model where we would store all the info:

```php
<?php
class Notification extends \ArrayObject
{
}
```

Then we have to do our notification action which would actually do all the job:

```php
<?php
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\SecuredNotifyRequest;

class StoreNotificationAction implements ActionInterface
{
    protected $notificationStorage;

    public function __constructor(StorageInterface $notificationStorage)
    {
        $this->notificationStorage = $notificationStorage;
    }

    public function execute($request)
    {
        $notification = $this->notificationStorage->createModel();
        foreach ($request->getNotification() as $name => $value) {
            $paymentNotification[$name] => $value;
        }

        $this->notificationStorage->updateModel($notification);
    }

    public function supports($request)
    {
        return $request instanceof SecuredNotifyRequest;
    }
}
```

In the code above we created payum custom action.
The main purpose of the action to store notification that with a request.
To do so it requires a storage.
Now we can update `config.php` described in [get it started](get-it-started.md).

```php
<?php
//config.php

use Payum\Core\Extension\StorageExtension;
use Payum\Core\Storage\FilesystemStorage;

$storeNotificationAction = new StoreNotificationAction(
    new FilesystemStorage('/path/to/storage', 'Notification')
);

$registry->getPayment('paypal')->addAction($storeNotificationAction);
```

Now we have to implement `notify.php` script which must be accessible from the internet.

```php
<?php
//notify.php

use Payum\Core\Request\SecuredNotifyRequest;

include 'config.php';

$token = $requestVerifier->verify();
$payment = $registry->getPayment($token->getPaymentName());

$payment->execute(new SecuredNotifyRequest($_REQUEST, $token));
```

## Setup Paypal IPN.

The code above could be reused by any payment.
Now I want to show changes need to enable Paypal IPN. To do so we have to modify `prepare.php` a bit:

```php
<?php

$notifyToken = $tokenStorage->createModel();
$notifyToken->setPaymentName('paypal');
$notifyToken->setDetails($storage->getIdentificator($paymentDetails));
$notifyToken->setTargetUrl('http://'.$_SERVER['HTTP_HOST'].'/notify.php?payum_token='.$doneToken->getHash());
$tokenStorage->updateModel($notifyToken);

$paymentDetails['NOTIFYURL'] = $notifyToken->getTargetUrl();
$storage->updateModel($paymentDetails);
```

Here we created one more token: `notify` and tell paypal to use its target url for notifications.

Back to [index](index.md).
