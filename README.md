# Payum
[![Build Status](https://travis-ci.org/Payum/Payum.png?branch=master)](https://travis-ci.org/Payum/Payum)
[![Total Downloads](https://poser.pugx.org/payum/payum/d/total.png)](https://packagist.org/packages/payum/payum)
[![Latest Stable Version](https://poser.pugx.org/payum/payum/version.png)](https://packagist.org/packages/payum/payum)

It is all about payments. The vision is to provide end solution keeping high level of a customization.
It would be handy tool not only for basic tasks like capture or refund but for recurring payments or instant notifications as well.

## Resources

* [Documentation](http://payum.org/doc#Payum)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## Examples

### Purchase

```php
<?php
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Model\Order;
use Payum\Offline\PaymentFactory as OfflinePaymentFactory;

$order = new Order;
$order->setTotalAmount(100);
$order->setCurrencyCode('USD');

$factory = OfflinePaymentFactory();
$payment = $factory->create();

$payment->execute(new Capture($order));
$payment->execute($status = new GetHumanStatus($order));

$status->isCaptured();
```

### Paypal Purchase

```php
<?php
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Model\Order;
use Payum\Paypal\ExpressCheckout\PaymentFactory as PaypalPaymentFactory;

$order = new Order;
$order->setTotalAmount(100);
$order->setCurrencyCode('USD');

$factory = PaypalPaymentFactory();
$payment = $factory->create();

try {
    $payment->execute(new Capture($order), true);
    $payment->execute($status = new GetHumanStatus($order));
    
    $status->isCaptured();
} catch (HttpRedirectReply $reply) {
    header("Location: ".$reply->getUrl());
    exit;
}
```


### Other operations.

```php
<?php
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Refund;

$payment->execute(new Authorize($order));

$payment->execute(new Refund($order));

$payment->execute(new Cancel($order));
```

### Storage

```php
<?php
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Extension\StorageExtension;
use Payum\Core\Model\Order;

$order = new Order;
$order->setTotalAmount(100);
$order->setCurrencyCode('USD');
$order->setNumber(uniqid());

$storage = new FilesystemStorage(sys_get_temp_dir(), get_class($order), 'number');

$payment->addExtension(new StorageExtension($storage));

// do execute
```

## Contributing

Payum is an open source, community-driven project. Pull requests are very welcome.

## Like it? Spread the word!

Star payum on [github](https://github.com/Payum/Payum) or [packagist](https://packagist.org/packages/payum/payum).

## License

Payum is released under the [MIT License](LICENSE).
