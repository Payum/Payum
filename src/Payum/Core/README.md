# Payum Core
[![Build Status](https://travis-ci.org/Payum/Core.png?branch=master)](https://travis-ci.org/Payum/Core)
[![Total Downloads](https://poser.pugx.org/payum/Core/d/total.png)](https://packagist.org/packages/payum/core)
[![Latest Stable Version](https://poser.pugx.org/payum/core/version.png)](https://packagist.org/packages/payum/core)

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
use Payum\Core\Model\Order;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Offline\PaymentFactory as OfflinePaymentFactory;

$order = new Order;
$order->setTotalAmount(100);
$order->setCurrencyCode('USD');

$payment = OfflinePaymentFactory::create();

if ($reply = $payment->execute(new Capture($order), true)) {
    if ($reply instanceof HttpRedirect) {
        header("Location: ".$reply->getUrl());
    } elseif () {
        echo $reply->getContent();
    } else {
        throw new \LogicException('Unsupported reply.', null, $reply);
    }
}
```

### Get status

```php
<?php
use Payum\Core\Request\GetHumanStatus;

$payment->execute($status = new GetHumanStatus($order));

echo $status->getValue();
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

Star payum on [github](https://github.com/Payum/Core) or [packagist](https://packagist.org/packages/payum/core).

## License

Payum is released under the [MIT License](LICENSE).
