# ISO4217 or Currency Details

> New code should not use any of this. Payum expresses amounts as [`moneyphp/money`](https://moneyphp.org)
> objects and resolves currencies through a `Money\Currencies` service — see [Money](../money.md). What
> follows is what a 1.x gateway still uses, and it keeps working.

Payum provides ability to get currency details listed in [ISO4217](http://en.wikipedia.org/wiki/ISO\_4217) specification. To get this information you have to execute a GetCurrency request with a currency code.

```php
<?php

$gateway = $container->get('payum')->getGatewayFactory('offline')->create();

$gateway->execute($currency = new \Payum\Core\GetCurrency('USD'));

echo $currency->alpha3;   // USD
echo $currency->name;     // US Dollar
echo $currency->exp;      // 2
echo $currency->country;  // US

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

Or directly using the Currency class:

```php
<?php

use Payum\Core\ISO4217\Currency;

$currency = Currency::createFromIso4217Alpha3('USD');

echo $currency->alpha3;   // USD
echo $currency->name;     // US Dollar
echo $currency->exp;      // 2
echo $currency->country;  // US
```

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
