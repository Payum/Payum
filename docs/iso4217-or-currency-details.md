# ISO4217 or Currency Details

> New code should not use any of this. Payum expresses amounts as [`moneyphp/money`](https://moneyphp.org)
> objects and resolves currencies through a `Money\Currencies` service — see [Money](money.md). What
> follows is what a 1.x gateway still uses, and it keeps working.

Payum provides ability to get currency details listed in [ISO4217](http://en.wikipedia.org/wiki/ISO\_4217) specification. To get this information you have to execute a GetCurrency request with a currency code.

```php
<?php

use Payum\Core\Request\GetCurrency;

$factory = new \Payum\Offline\OfflineGatewayFactory();
$gateway = $factory->create();

$gateway->execute($currency = new GetCurrency('USD'));

echo $currency->alpha3;   // USD
echo $currency->name;     // US Dollar
echo $currency->exp;      // 2
echo $currency->country;  // US

// and so on...
```

The request is a plain object filled in by the action, so the details are read off public properties
rather than through getters.

Or inside another action:

```php
<?php

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetCurrency;

class FooAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    
    public function execute($request)
    {
        $this->gateway->execute($currency = new GetCurrency('USD'));

        echo $currency->alpha3;   // USD
        echo $currency->name;     // US Dollar
        echo $currency->exp;      // 2
        echo $currency->country;  // US
    }
}
```

Or directly ISO4217 service:

```php
<?php

use Payum\Core\ISO4217\Currency;

$currency = Currency::createFromIso4217Alpha3('USD');

echo $currency->getAlpha3();  // USD
echo $currency->getName();    // US Dollar
echo $currency->getExp();     // 2
echo $currency->getCountry(); // US
```

### Currencies ISO 4217 does not list

`GetCurrency` also answers for a currency registered on the gateway's `Money\Currencies` service — a
crypto currency, a loyalty point — taking the exponent from there. `alpha3` and `exp` are the useful part:
a currency outside the standard has no numeric code or country, and its name comes back as the code.

```php
$gateway->execute($currency = new GetCurrency('BTC'));

echo $currency->exp;  // 8
```

See [Money](money.md) for how to register one.

### Next

* [Money](money.md).
* [The architecture](the-architecture.md).
* [Supported gateways](supported-gateways.md).
* [Storages](storages.md).

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
