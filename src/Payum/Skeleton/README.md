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

2. Rename the package to your own: the `Payum\Skeleton` namespace, the `Skeleton` prefix of the class names, and
   `payum/skeleton` in `composer.json`. Leave `Payum\Core` as it is. The steps below use the names before renaming.
3. Add the PSP's credentials to `SkeletonConfig` as `readonly` constructor arguments, and validate them in its
   constructor.
4. Register the gateway with its config:

```php
<?php

use Payum\Core\PayumBuilder;
use Payum\Skeleton\SkeletonConfig;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('skeleton', new SkeletonConfig(sandbox: true))
    ->getPayum()
;
```

5. Implement every handler method that throws `Payum\Core\Exception\NotImplementedException`. A handler takes the
   API through its constructor, and Payum autowires it:

```php
public function __construct(
    private readonly SkeletonApi $api,
) {
}
```

Remove the handlers for operations your PSP does not support from `SkeletonGateway::handlers()`.

## Resources

* [Site](https://payum.dev/)
* [Documentation](https://payum.dev/docs/develop-gateway-with-payum)
* [Questions](http://stackoverflow.com/questions/tagged/payum)
* [Issue Tracker](https://github.com/Payum/Payum/issues)
* [Twitter](https://twitter.com/payumphp)

## License

Skeleton is released under the [MIT License](LICENSE).
