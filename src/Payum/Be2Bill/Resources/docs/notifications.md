# Notifications
Be2Bill supports push notifications and can let you know when something is changed on your payment.
You can configure an url in their backend, one for all payments.

## Configure

First, we have to create object `NotifyPaymentAction` and properly configure it.
The $storage variable is an instance of `StorageInterface` and it has to be able to find your model by ORDERID sent in notification.
The $idField variable is a field we use to query the payment.

```php
// config.php

<?php
$offsiteBe2billFactory->create(array(
   'payum.action.notify_payment' = new NotifyPaymentAction($paymentStorage, $idField),
));
```

## notify script.

Notify scripts, validates the request and store changes to the payment model.
If you want to dispatch an event on success payment add an extension and check status there.

```php
<?php
// notify.php

include 'config.php';

$gateway = $payum->getGateway('be2bill_offsite');
$gateway->execute(new Notify(null));
```

Back to [index](index.md).