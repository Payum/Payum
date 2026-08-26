# Develop a custom Payum gateway

A gateway describes itself and ships handlers that answer commands. This page is the short version; the [Gateways](gateways/README.md) chapter is the full reference.

_**Note**: gateways written for 1.x keep working and are still supported. If you are porting one, read the_ [_migration guide_](gateways/migrating-from-v1.md)_._

### 1. Scaffold the package

```bash
$ composer create-project payum/skeleton
```

Replace `payum` with your vendor name and `skeleton` with the gateway name.

### 2. The config

Credentials, as a value object that validates itself:

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
        public readonly bool $sandbox = false,
    ) {
        if ('' === $secretKey) {
            throw new LogicException('Acme needs a secret key.');
        }
    }

    public function getGatewayClass(): string
    {
        return AcmeGateway::class;
    }
}
```

### 3. The api

The only thing that talks to the PSP. Every argument here is already a container entry, so it is autowired with no service definition:

```php
<?php
namespace Acme\Payum\Api;

use Acme\Payum\Config\AcmeConfig;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class AcmeApi
{
    public function __construct(
        private readonly AcmeConfig $config,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
    ) {
    }

    public function createCheckout(array $parameters): array { /* … */ }
    public function retrieveCheckout(string $id): array { /* … */ }
}
```

### 4. A handler

One interface per command, so `handle()` is typed on both sides. Capture is re-entrant: the PSP returns the customer to the capture token's own URL, so the same command runs again and the handler reads the state it wrote to know which pass it is on.

```php
<?php
namespace Acme\Payum\Handler;

use Acme\Payum\Api\AcmeApi;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\NextAction\Redirect;

final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(private readonly AcmeApi $api)
    {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();

        if ($state['checkout_id']) {
            $checkout = $this->api->retrieveCheckout($state['checkout_id']);

            return 'paid' === $checkout['status']
                ? CaptureResult::captured($checkout['charge_id'], $checkout['amount'])
                : CaptureResult::pending(raw: $checkout);
        }

        $checkout = $this->api->createCheckout([
            'return_url' => $context->token()?->getTargetUrl(),
            'amount' => $context->amount()?->getAmount(),
            'currency' => $context->payment()?->getCurrencyCode(),
        ]);

        $state['checkout_id'] = $checkout['id'];

        return CaptureResult::pending(new Redirect($checkout['url']));
    }
}
```

### 5. The gateway

```php
<?php
namespace Acme\Payum;

use Acme\Payum\Config\AcmeConfig;
use Acme\Payum\Handler\CaptureHandler;
use League\Uri\Uri;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;

final class AcmeGateway implements GatewayInterface
{
    public function name(): string        { return 'Acme Payments'; }
    public function logo(): Logo          { return Logo\Path::create(__DIR__ . '/Resources/logo.svg'); }
    public function websiteUrl(): Uri     { return Uri::new('https://developer.acme.test'); }
    public function configClass(): string { return AcmeConfig::class; }

    public function handlers(): array     { return [CaptureHandler::class]; }
}
```

Listing the handler is the whole mapping — Payum reads which handler interface it implements and takes the command from there. It also derives `Capability::Capture` from that, so capabilities cannot drift from what the gateway actually does.

No constructor arguments, so an application can read the name, logo and config class before any credentials exist.

### 6. Use it

```php
<?php
use Payum\Core\Command\CaptureCommand;
use Payum\Core\PayumBuilder;
use Acme\Payum\Config\AcmeConfig;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig('sk_test_…', sandbox: true))
    ->getPayum();

$result = $payum->getGateway('acme')->execute(CaptureCommand::forToken($token));
```

### Next

* [Defining a gateway](gateways/defining-a-gateway.md) — metadata and capabilities
* [Handlers](gateways/handlers.md) — the context, state, and re-entrant capture
* [Results](gateways/results.md) — next actions, status, failures
* [Services](gateways/services.md) — overriding and decorating

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
