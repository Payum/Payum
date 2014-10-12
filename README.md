# PayumBundle 
[![Build Status](https://travis-ci.org/Payum/PayumBundle.png?branch=master)](https://travis-ci.org/Payum/PayumBundle) 
[![Total Downloads](https://poser.pugx.org/payum/payum-bundle/d/total.png)](https://packagist.org/packages/payum/payum-bundle) 
[![Latest Stable Version](https://poser.pugx.org/payum/payum-bundle/version.png)](https://packagist.org/packages/payum/payum-bundle)
[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/Payum/payumbundle/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

The bundle integrate [payum](https://github.com/Payum/Payum) into [symfony](http://www.symfony.com) framework.
It already supports [+35 payments](http://payum.org/doc/Core/supported-payments).
Provide nice configuration layer, secured capture controller, storages integration and lots of more features.

[Sylius e-commerce platform](http://sylius.com) base its payment solutions on top of the bundle.

## Resources

* [Documentation](http://payum.org/doc#PayumBundle)
* [Sandbox](http://sandbox.payum.forma-dev.com)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/PayumBundle/issues)
* [Twitter](https://twitter.com/payumphp)

## Examples

### Configure:

```yaml
payum:
    storages:
        Payum\Core\Model\Order:
            filesystem:
                storage_dir: %kernel.root_dir%/Resources/payments
                id_property: number

    security:
        token_storage:
            Payum\Core\Model\Token:
                storage_dir: %kernel.root_dir%/Resources/payments
                id_property: hash
                
    contexts:
        offline:
            offline: ~
```

### Purchase

```php
<?php
use Payum\Core\Model\Order;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;
use Payum\Offline\PaymentFactory as OfflinePaymentFactory;

$order = new Order;
$order->setNumber(uniqid());
$order->setCurrencyCode('EUR');
$order->setTotalAmount(123); // 1.23 EUR
$order->setDescription('A description');
$order->setClientId('anId');
$order->setClientEmail('foo@example.com');

$payment = $this->get('payum')->getPayment('offline');
$payment->execute(new Capture($order));
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

## Contributing

PayumBundle is an open source, community-driven project. Pull requests are very welcome.

## Like it? Spread the world!

Star PayumBundle on [github](https://github.com/Payum/PayumBundle) or [packagist](https://packagist.org/packages/payum/payum-bundle).

## License

The bundle is released under the [MIT License](Resources/meta/LICENSE).
