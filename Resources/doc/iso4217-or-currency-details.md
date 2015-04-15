# ISO4217 or Currency Details. 

Payum provides ability to get currency details listed in [ISO4217](http://en.wikipedia.org/wiki/ISO_4217) specification.
To get this information you have to execute a GetCurrency request with a currency code.


```php
<?php

$gateway = $container->get('payum')->getGatewayFactory('offline')->create();

$gateway->execute($currency = new \Payum\Core\GetCurrency('USD'));

echo $currency->alpha3;  // USD
echo $currency->name;    // US Dollar
echo $currency->exp;     // 2
echo $currency->country; // US

// and so on...
```

Or inside another action:

```php
<?php

class FooAction extends GatewayAwareAction
{
    public function execute($request)
    {
        $this->gateway->execute($currency = new \Payum\Core\GetCurrency('USD'));
    }
}
```

Or directly ISO4217 service:

```php
<?php

/** @var \Payum\ISO4216\ISO4217 $iso4217
$iso4217 = $container->get('payum.iso4217');

/** @var \Payum\ISO4216\Currency $currency **/
$currency = $iso4217->findByAlpha3('USD');

echo $currency->getAlpha3();  // USD
echo $currency->getName();    // US Dollar
echo $currency->getExp();     // 2
echo $currency->getCountry(); // US
```

## Next 

* [The architecture](the-architecture.md).
* [Supported gateways](supported-gateways.md).
* [Storages](storages.md).
* [Capture script](capture-script.md).
* [Authorize script](authorize-script.md).
* [Done script](done-script.md).

Back to [index](index.md).
