# PayumBundle 
[![Build Status](https://travis-ci.org/Payum/PayumBundle.png?branch=master)](https://travis-ci.org/Payum/PayumBundle) 
[![Total Downloads](https://poser.pugx.org/payum/payum-bundle/d/total.png)](https://packagist.org/packages/payum/payum-bundle) 
[![Latest Stable Version](https://poser.pugx.org/payum/payum-bundle/version.png)](https://packagist.org/packages/payum/payum-bundle)

The bundle  integrate [payum](https://github.com/Payum/Payum) into [symfony](http://www.symfony.com) framework.
It already supports [+35 gateways](http://payum.org/doc/1.0/Core/supported-gateways).
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
        Payum\Core\Model\Payment:
            filesystem:
                storage_dir: %kernel.root_dir%/Resources/payments
                id_property: number

    security:
        token_storage:
            Payum\Core\Model\Token:
                storage_dir: %kernel.root_dir%/Resources/gateways
                id_property: hash
                
    gateways:
        offline:
            offline: ~
```

### Purchase

```php
<?php
use Payum\Core\Model\Payment;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Capture;

$payment = new Payment;
$payment->setNumber(uniqid());
$payment->setCurrencyCode('EUR');
$payment->setTotalAmount(123); // 1.23 EUR
$payment->setDescription('A description');
$payment->setClientId('anId');
$payment->setClientEmail('foo@example.com');

$gateway = $this->get('payum')->getGateway('offline');
$gateway->execute(new Capture($payment));
```

### Get status

```php
<?php
use Payum\Core\Request\GetHumanStatus;

$gateway->execute($status = new GetHumanStatus($payment));

echo $status->getValue();
```

### Other operations.

```php
<?php
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Refund;

$gateway->execute(new Authorize($payment));

$gateway->execute(new Refund($payment));

$gateway->execute(new Cancel($payment));
```

## Contributing

PayumBundle is an open source, community-driven project. Pull requests are very welcome.

## Like it? Spread the word!

Star PayumBundle on [github](https://github.com/Payum/PayumBundle) or [packagist](https://packagist.org/packages/payum/payum-bundle).

## Donate

<a href='https://pledgie.com/campaigns/30526'><img alt='Click here to lend your support to: Your private payment processing server. Setup it once and rule them all and make a donation at pledgie.com !' src='https://pledgie.com/campaigns/30526.png?skin_name=chrome' border='0' ></a>

## License

The bundle is released under the [MIT License](Resources/meta/LICENSE).
