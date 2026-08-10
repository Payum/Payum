# Configuration

Credentials live in a value object that validates itself. A malformed secret key fails when the application boots, with a stack trace pointing at your own wiring, rather than deep inside a handler at capture time.

```php
<?php
namespace Acme\Payum\Config;

use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\LogicException;
use Acme\Payum\AcmeGateway;

final class AcmeConfig implements GatewayConfig
{
    public function __construct(
        public readonly string $secretKey,
        public readonly string $publishableKey,
        public readonly bool $sandbox = false,
    ) {
        if ('' === $secretKey || '' === $publishableKey) {
            throw new LogicException('Acme needs both a secret key and a publishable key.');
        }
    }

    public function getGatewayClass(): string
    {
        return AcmeGateway::class;
    }

    public function withSandbox(bool $sandbox): self
    {
        return new self($this->secretKey, $this->publishableKey, $sandbox);
    }
}
```

`getGatewayClass()` is the link Payum follows from a config to the gateway it configures. `AcmeGateway::configClass()` is the same edge in the other direction, for an admin screen that has a gateway but no config yet. Keep them in agreement.

Make it immutable. `withSandbox()` returning a new instance means nothing can quietly repoint a live gateway at the sandbox halfway through a request.

### Registering a gateway

```php
<?php
use Payum\Core\PayumBuilder;
use Acme\Payum\Config\AcmeConfig;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig('sk_live_…', 'pk_live_…'))
    ->getPayum();
```

That is all the wiring there is. The config names its gateway, the gateway names its handlers, and Payum builds the container from there.

Passing a config to the wrong gateway is caught immediately:

```php
$payum = (new PayumBuilder())
    ->registerGateway('acme', new StripeCheckoutConfig(…))
    ->getPayum();

// LogicException: Acme\Payum\AcmeGateway is configured by
// Acme\Payum\Config\AcmeConfig, but Payum\Stripe\Config\StripeCheckoutConfig was given.
```

### Several instances of one gateway

Register the same gateway under different names with different configs. Each gets its own container, so each gets its own api object, while both share the HTTP client and the token storage.

```php
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme_eu', new AcmeConfig('sk_eu_…', 'pk_eu_…'))
    ->registerGateway('acme_us', new AcmeConfig('sk_us_…', 'pk_us_…'))
    ->getPayum();
```

### Reading it back

The config is a container entry, so anything in the gateway's container can ask for it — by its own class, or by the interface:

```php
final class AcmeApi
{
    public function __construct(private readonly AcmeConfig $config) {}
}
```

### Listing gateways before they are configured

Because a gateway takes no constructor arguments, an application can describe one it has never configured. This is what an "add a payment method" screen renders from:

```php
<?php
$gateway = new AcmeGateway();

$gateway->name();         // 'Acme Payments'
$gateway->logo();         // Logo\Url
$gateway->websiteUrl();   // League\Uri\Uri
$gateway->configClass();  // Acme\Payum\Config\AcmeConfig — the form to render
```

Next: [Commands](commands.md).
