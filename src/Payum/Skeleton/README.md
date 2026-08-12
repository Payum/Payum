<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://github.com/sponsors/payum)

---

# Skeleton

The Payum extension to rapidly build new extensions.

1. Create new project

```bash
$ composer create-project payum/skeleton
```

2. Replace all occurrences of `payum` with your vendor name. It may be your github name, for now let's say you choose: `acme`.
3. Replace all occurrences of `skeleton` with a payment gateway name. For example Stripe, Paypal etc. For now let's say you choose: `paypal`.
4. Register a gateway factory to the payum's builder and create a gateway:

```php
<?php

use Payum\Core\PayumBuilder;
use Payum\Core\GatewayFactoryInterface;

$defaultConfig = [];

$payum = (new PayumBuilder)
    ->addGatewayFactory('paypal', function(array $config, GatewayFactoryInterface $coreGatewayFactory) {
        return new \Acme\Paypal\PaypalGatewayFactory($config, $coreGatewayFactory);
    })

    ->addGateway('paypal', [
        'factory' => 'paypal',
        'sandbox' => true,
    ])

    ->getPayum()
;
```

5. While using the gateway implement all method where you get `Not implemented` exception:

```php
<?php

use Payum\Core\Request\Capture;

$paypal = $payum->getGateway('paypal');

$model = new \ArrayObject([
  // ...
]);

$paypal->execute(new Capture($model));
```

## Resources

* [Site](https://docs.payum.dev/)
* [Documentation](https://github.com/Payum/Payum/blob/2.x/docs/index.md#general)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## License

Skeleton is released under the [MIT License](LICENSE).
