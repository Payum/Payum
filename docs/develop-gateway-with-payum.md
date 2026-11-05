# Build a gateway

A gateway connects Payum to one PSP (payment service provider, the company that moves the money). This
tutorial builds `acme/payum-acme`, a gateway for a made-up PSP called Acme Payments, and runs a payment
through it. Acme works the way most hosted checkouts do: you create a checkout over its API, send the
customer to Acme's payment page, and Acme sends them back to you when they are done.

The [Gateways](gateways/README.md) section is the reference for every piece used here.

## What you build

The finished package has five files:

```
acme/payum-acme/
├── composer.json
└── src/
    ├── AcmeConfig.php        the credentials
    ├── AcmeApi.php           the only class that talks to Acme
    ├── AcmeGateway.php       what the gateway is called and what it can do
    ├── Handler/
    │   └── CaptureHandler.php
    └── Resources/
        └── logo.svg
```

An application then registers it with one line:

```php
$builder->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true));
```

## Create the package

Start with a `composer.json` that requires Payum and autoloads the `Acme\Payum` namespace from `src/`:

```json
{
    "name": "acme/payum-acme",
    "description": "Acme Payments gateway for Payum",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^8.1",
        "payum/core": "^2.0",
        "league/uri": "^7.0"
    },
    "require-dev": {
        "php-http/guzzle7-adapter": "^1.0"
    },
    "autoload": {
        "psr-4": { "Acme\\Payum\\": "src/" }
    }
}
```

Then install it:

```bash
composer install
```

Payum sends HTTP requests through whichever PSR-18 client the application installs, so the gateway
requires none. The Guzzle adapter in `require-dev` is only there so you can run the package on its own.
`league/uri` is required for the `Uri` class the gateway returns as its website.

## Describe the credentials

A config class holds what an application must supply to use the gateway. For Acme that is an API key
and a switch for the sandbox. Create `src/AcmeConfig.php`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Payum\Core\Config\GatewayConfig;
use Payum\Core\Exception\InvalidArgumentException;

final class AcmeConfig implements GatewayConfig
{
    public function __construct(
        public readonly string $apiKey,
        public readonly bool $sandbox = false,
    ) {
        if ('' === $apiKey) {
            throw new InvalidArgumentException('Acme needs an API key.');
        }
    }

    public function getGatewayClass(): string
    {
        return AcmeGateway::class;
    }

    public function baseUrl(): string
    {
        return $this->sandbox ? 'https://sandbox.acme.test/v1' : 'https://api.acme.test/v1';
    }
}
```

`getGatewayClass()` tells Payum which gateway this config belongs to. The constructor checks the key,
so a missing credential fails when the application starts, not halfway through a payment.

## Talk to the PSP

The API class is the only class that sends requests to Acme. Create `src/AcmeApi.php`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Payum\Core\Exception\RuntimeException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class AcmeApi
{
    public function __construct(
        private readonly AcmeConfig $config,
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    public function createCheckout(array $parameters): array
    {
        return $this->send('POST', '/checkouts', $parameters);
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveCheckout(string $id): array
    {
        return $this->send('GET', '/checkouts/' . rawurlencode($id));
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array<string, mixed>
     */
    private function send(string $method, string $path, ?array $body = null): array
    {
        $request = $this->requestFactory
            ->createRequest($method, $this->config->baseUrl() . $path)
            ->withHeader('Authorization', 'Bearer ' . $this->config->apiKey)
            ->withHeader('Accept', 'application/json');

        if (null !== $body) {
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($this->streamFactory->createStream(json_encode($body, JSON_THROW_ON_ERROR)));
        }

        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() >= 400) {
            throw new RuntimeException(sprintf('Acme answered %d to %s %s.', $response->getStatusCode(), $method, $path));
        }

        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }
}
```

You never register this class. Payum autowires it: the config is the one the application registered,
and the HTTP client and the PSR-17 factories are services Payum shares with every gateway.

## Take the payment

A handler does one operation. Capturing (taking the customer's money) is the one every gateway needs.
Create `src/Handler/CaptureHandler.php`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum\Handler;

use Acme\Payum\AcmeApi;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\LogicException;
use Payum\Core\Handler\CaptureHandlerInterface;
use Payum\Core\Handler\Context;
use Payum\Core\Result\CaptureResult;
use Payum\Core\Result\Failure;
use Payum\Core\Result\FailureReason;
use Payum\Core\Result\NextAction\Redirect;
use Payum\Core\Result\PaymentStatus;

final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(
        private readonly AcmeApi $api,
    ) {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();

        if ($state['checkout_id']) {
            $checkout = $this->api->retrieveCheckout($state['checkout_id']);

            return match ($checkout['status']) {
                'paid' => CaptureResult::captured($checkout['charge_id'], raw: $checkout),
                'canceled' => CaptureResult::canceled(raw: $checkout),
                'expired' => CaptureResult::failed(new Failure(FailureReason::Unknown, 'expired'), PaymentStatus::Expired, $checkout),
                default => CaptureResult::pending(raw: $checkout),
            };
        }

        $token = $context->token();
        $amount = $context->amount();

        if (null === $token || null === $amount) {
            throw new LogicException('An Acme capture needs a capture token and a payment with an amount.');
        }

        $checkout = $this->api->createCheckout([
            'amount' => (int) $amount->getAmount(),
            'currency' => $amount->getCurrency()->getCode(),
            'return_url' => $token->getTargetUrl(),
        ]);

        $state['checkout_id'] = $checkout['id'];

        return CaptureResult::pending(new Redirect($checkout['url']));
    }
}
```

Payum runs this handler twice for one payment:

1. The first time, there is no checkout yet. The handler creates one, stores its id in the state, and
   returns a pending result with a `Redirect`. Payum sends the customer to Acme.
2. Acme sends the customer back to the token's target URL, which is the URL that started the capture.
   The same command runs again. This time the state holds a checkout id, so the handler asks Acme how
   the checkout ended and reports that.

`$context->state()` is the gateway's own data for this payment. Payum saves it on the payment after the
handler returns, so it is there on the second run. `$context->amount()` is the amount to capture as a
`Money`, in minor units (cents for EUR).

The handler never sets the payment's status itself. It returns a result that says what happened, and
Payum records it.

## Describe the gateway

The gateway class names the gateway, its config and its handlers. Create `src/AcmeGateway.php`:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Acme\Payum\Handler\CaptureHandler;
use League\Uri\Uri;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;

final class AcmeGateway implements GatewayInterface
{
    public function name(): string
    {
        return 'Acme Payments';
    }

    public function logo(): Logo
    {
        return Logo\Path::create(__DIR__ . '/Resources/logo.svg');
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://developer.acme.test');
    }

    public function configClass(): string
    {
        return AcmeConfig::class;
    }

    public function handlers(): array
    {
        return [
            CaptureHandler::class,
        ];
    }
}
```

Put an SVG logo at `src/Resources/logo.svg`. Listing `CaptureHandler` is all the wiring a handler
needs: Payum sees that it implements `CaptureHandlerInterface` and sends every `CaptureCommand` to it.

Give the gateway class no constructor arguments. Payum creates it without any configuration to read its
name and logo, for example to show a list of installed gateways before anyone has entered credentials.

## Run a capture

You can run a full capture before you have an Acme account, by giving Payum an HTTP client that answers
the way Acme would. Create `tests/FakeAcmeClient.php`:

```php
<?php

declare(strict_types=1);

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class FakeAcmeClient implements ClientInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    public array $checkouts = [];

    public function __construct(
        private readonly ResponseFactoryInterface $responses,
        private readonly StreamFactoryInterface $streams,
    ) {
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();

        if ('POST' === $request->getMethod() && str_ends_with($path, '/checkouts')) {
            $id = 'chk_' . (count($this->checkouts) + 1);

            $this->checkouts[$id] = [
                'id' => $id,
                'status' => 'open',
                'url' => 'https://pay.acme.test/' . $id,
            ] + json_decode((string) $request->getBody(), true);
        }

        $checkout = $this->checkouts[$id ?? basename($path)];

        return $this->responses->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->streams->createStream(json_encode($checkout)));
    }
}
```

Then create `try-it.php` at the root of the package. It registers the gateway, captures a payment of
10.00 EUR, plays the customer paying on Acme's page, and captures again:

```php
<?php

declare(strict_types=1);

use Acme\Payum\AcmeConfig;
use Http\Discovery\Psr17FactoryDiscovery;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Psr\Http\Client\ClientInterface;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/tests/FakeAcmeClient.php';

$_SERVER['HTTP_HOST'] = 'shop.example.com';

$acme = new FakeAcmeClient(
    Psr17FactoryDiscovery::findResponseFactory(),
    Psr17FactoryDiscovery::findStreamFactory(),
);

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(ClientInterface::class, $acme)
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();

$payment = new Payment();
$payment->setNumber(uniqid());
$payment->setTotalAmount(1000);
$payment->setCurrencyCode('EUR');

$token = $payum->prepare('acme', $payment, 'done.php');
$gateway = $payum->getGateway('acme');

$result = $gateway->execute(CaptureCommand::forToken($token));

echo $result->status->value, ' ', $result->next->url, PHP_EOL;

$acme->checkouts['chk_1']['status'] = 'paid';
$acme->checkouts['chk_1']['charge_id'] = 'ch_1';

$result = $gateway->execute(CaptureCommand::forToken($token));

echo $result->status->value, ' ', $result->transactionId, PHP_EOL;
```

Run it:

```bash
php try-it.php
```

It prints:

```
pending https://pay.acme.test/chk_1
captured ch_1
```

`addGlobalService()` replaces the HTTP client for every gateway, which is how the fake client reaches
`AcmeApi`. `prepare()` stores the payment and creates the capture token (a single-use URL that starts
the capture). The script sets `HTTP_HOST` because Payum builds that URL from the current request, and a
command-line script has none.

The second capture finds the checkout id the first one stored, so it reads the checkout instead of
creating another one. That is the redirect round trip, without the browser.

## Use it in an application

An application installs the package and registers the gateway under a name of its choice:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Keep the real key out of your code. Read it from the environment or your framework's secrets store, and
pass `sandbox: false` in production.

From there the application takes payments the same way with every gateway: it prepares a payment, sends
the customer to the capture URL, and `Payum::capture()` runs the handler and turns the `Redirect` into a
response. See [Payment operations](payment-operations.md) for the scripts an application needs.

## Next steps

The gateway can take a payment. Most gateways also need these:

* **More operations.** Add a handler for each one the PSP offers: refund, authorize, cancel, payout and
  sync. See [Handlers](gateways/handlers.md) and [Commands](gateways/commands.md).
* **Webhooks.** Acme can tell you when a payment changes after the customer has left. Ship a notify
  handler to receive those messages. See [Webhooks](gateways/webhooks.md).
* **Pages of your own.** A gateway that shows a card form instead of redirecting returns a template.
  See [Templates](gateways/templates.md).
* **Capabilities.** Tell applications what the gateway supports beyond its handlers, such as partial
  refunds. See [Defining a gateway](gateways/defining-a-gateway.md).
* **Services.** Build a service that autowiring cannot, or replace one for this gateway only. See
  [Services](gateways/services.md).

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
