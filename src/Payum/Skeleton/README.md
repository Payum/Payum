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
4. Add the PSP's credentials to `PaypalConfig` as `readonly` constructor arguments, and validate them in its constructor.
5. Register the gateway with its config:

```php
<?php

use Acme\Paypal\PaypalConfig;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('paypal', new PaypalConfig(sandbox: true))
    ->getPayum()
;
```

6. Implement every handler method that throws `Not implemented`. A handler takes the API through its constructor, and Payum autowires it:

```php
public function __construct(
    private readonly PaypalApi $api,
) {
}
```

Remove the handlers for operations your PSP does not support from `PaypalGateway::handlers()`.

## Resources

* [Site](https://payum.dev/)
* [Documentation](https://payum.dev/docs/develop-gateway-with-payum)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## License

Skeleton is released under the [MIT License](LICENSE).
