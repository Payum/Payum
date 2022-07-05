<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Payum Bundle. ISO4217 or Currency Details. 

Payum provides ability to get currency details listed in [ISO4217](http://en.wikipedia.org/wiki/ISO_4217) specification.
To get this information you have to execute a GetCurrency request with a currency code.


```php
<?php

$gateway = $container->get('payum')->getGatewayFactory('offline')->create();

$gateway->execute($currency = new \Payum\Core\GetCurrency('USD'));

echo $currency->getAlpha3();  // USD
echo $currency->getName();    // US Dollar
echo $currency->getExp();     // 2
echo $currency->getCountry(); // US

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

echo $currency->getAlpha3();  // USD
echo $currency->getName();    // US Dollar
echo $currency->getExp();     // 2
echo $currency->getCountry(); // US
```

* [Back to index](../index.md).
