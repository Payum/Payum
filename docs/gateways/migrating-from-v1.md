# Migrate a gateway from 1.x

A gateway package written for Payum 1.x keeps working on 2.x: its factory, its actions and the replies
they throw are deprecated, and removed in 3.0. This page is for gateway authors. It shows how to port a
1.x gateway to the 2.x model of a gateway class, a config class and handlers, either in one go or one
operation at a time.

Read [Gateways](README.md) and [Build a gateway](../develop-gateway-with-payum.md) first for the target
model. This page covers only how to get there from 1.x. If you are upgrading an application rather than a
gateway, read [Upgrade from 1.x](../upgrading.md).

## What maps to what

| 1.x | 2.x |
| :--- | :--- |
| `GatewayFactory::populateConfig()` | a gateway class implementing `Payum\Core\Gateway\GatewayInterface`, see [Defining a gateway](defining-a-gateway.md) |
| `payum.default_options`, `payum.required_options` | constructor arguments of a config class implementing `Payum\Core\Config\GatewayConfig`, see [Configuration](configuration.md) |
| `payum.factory_title` | `GatewayInterface::name()` |
| `payum.action.*` keys | `GatewayInterface::handlers()` |
| `ActionInterface::execute($request)` and `supports()` | one handler per command, such as `CaptureHandlerInterface::handle($command, $context)`, see [Handlers](handlers.md) |
| `Payum\Core\Request\Capture` and the other operation requests | `Payum\Core\Command\CaptureCommand` and the other commands, see [Commands](commands.md) |
| `ArrayObject::ensureArrayObject($request->getModel())` | `$context->state()` |
| `throw new HttpRedirect($url)` | `return CaptureResult::pending(new Redirect($url))`, see [Results](results.md) |
| `throw new HttpPostRedirect($url, $fields)` | `return CaptureResult::pending(new PostRedirect($url, $fields))` |
| `throw new HttpResponse($html)` for a page | `return CaptureResult::pending(new RenderTemplate($template))`, see [Templates](templates.md) |
| a status action answering `GetHumanStatus` | the status set by the result, as in `CaptureResult::captured($transactionId)` |
| a `Convert` action | read `$context->payment()` and `$context->amount()` in the handler |
| `$this->gateway->execute(new GetHttpRequest())` | `$context->httpRequest()`, a PSR-7 request |
| `$this->gateway->execute(new GetToken($hash))` | `$context->token()` |
| `$this->gateway->execute(new GetCurrency($code))` | a `Money\Currencies` taken as a constructor argument |
| `GenericTokenFactoryAwareTrait` | `$context->tokens()`, or `$context->notifyUrl()` for a notify URL |
| `$this->gateway->execute(new SomeApiRequest($details))` | a method on your API class |
| `GatewayAwareTrait` with `$this->gateway->execute()` for an operation | `$context->execute($command)` |
| `ApiAwareInterface`, `payum.api` | the API as a constructor argument, autowired, see [Services](services.md) |
| `payum.paths` | `Payum\Core\Gateway\DeclaresTemplates::templateNamespaces()` |
| `payum.extension.*` keys | middleware, declared with `Payum\Core\Gateway\DeclaresMiddleware`, see [Middleware](middleware.md) |
| a notify action | `Payum\Core\Handler\NotifyHandlerInterface`, see [Webhooks](webhooks.md) |

A layout that mirrors the 1.x one keeps the map easy to follow:

```text
AcmeGatewayFactory.php     ->  AcmeGateway.php and AcmeConfig.php
Action/CaptureAction.php   ->  Handler/CaptureHandler.php
Action/Api/*Action.php     ->  methods on AcmeApi.php
Action/StatusAction.php    ->  nothing, the results set the status
```

## The 1.x gateway

The steps below port this 1.x gateway. Its factory registers a capture action, a refund action, a status
action and a convert action, and builds the API from the options:

```php
<?php

namespace Acme\Payum;

use Acme\Payum\Action\CaptureAction;
use Acme\Payum\Action\ConvertPaymentAction;
use Acme\Payum\Action\RefundAction;
use Acme\Payum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class AcmeGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'acme',
            'payum.factory_title' => 'Acme',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = ['api_key' => '', 'sandbox' => true];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['api_key'];

            $config['payum.api'] = static function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new AcmeApi((array) $config);
            };
        }
    }
}
```

Its capture action reads the HTTP request through a sub-request, creates a checkout at the provider and
redirects the customer there. When the customer comes back, it reads the checkout:

```php
<?php

namespace Acme\Payum\Action;

use Acme\Payum\AcmeApi;
use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;

class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = AcmeApi::class;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if (isset($httpRequest->query['cancelled'])) {
            $details['status'] = 'canceled';

            return;
        }

        if (! $details['checkout_id']) {
            $details['return_url'] = $request->getToken()->getTargetUrl();
            $details->replace($this->api->createCheckout($details->toUnsafeArray()));

            throw new HttpRedirect($details['checkout_url']);
        }

        $details->replace($this->api->getCheckout($details['checkout_id']));
    }

    public function supports($request): bool
    {
        return $request instanceof Capture && $request->getModel() instanceof ArrayAccess;
    }
}
```

## Port the whole gateway

Work through these steps in order. Each one replaces one part of the factory.

### Replace the options with a config class

Each required option becomes a constructor argument, and each default becomes the argument's default:

```php
<?php

namespace Acme\Payum;

use InvalidArgumentException;
use Payum\Core\Config\GatewayConfig;

final class AcmeConfig implements GatewayConfig
{
    public function __construct(
        public readonly string $apiKey,
        public readonly bool $sandbox = true,
    ) {
        if ('' === $apiKey) {
            throw new InvalidArgumentException('Acme needs an API key.');
        }
    }

    public function getGatewayClass(): string
    {
        return AcmeGateway::class;
    }
}
```

Validation that `validateNotEmpty()` did when the API was first built now runs when the application
creates the config, at boot. See [Configuration](configuration.md).

### Write the gateway class

The gateway class replaces the factory. It describes the gateway and lists its handlers:

```php
<?php

namespace Acme\Payum;

use Acme\Payum\Handler\CaptureHandler;
use Acme\Payum\Handler\RefundHandler;
use League\Uri\Uri;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;

final class AcmeGateway implements GatewayInterface
{
    public function name(): string
    {
        return 'Acme';
    }

    public function logo(): Logo
    {
        return Logo\Url::create('https://acme.test/logo.svg');
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }

    public function configClass(): string
    {
        return AcmeConfig::class;
    }

    public function handlers(): array
    {
        return [CaptureHandler::class, RefundHandler::class];
    }
}
```

`payum.factory_title` becomes `name()`, and the `payum.action.*` keys become `handlers()`. There is no
factory name: the application picks the name it registers the gateway under. See
[Defining a gateway](defining-a-gateway.md) for the metadata and for declaring capabilities.

### Give the API the config

Change the API's constructor to take the config object instead of the options array:

```php
public function __construct(
    private readonly AcmeConfig $config,
    private readonly ClientInterface $httpClient,
    private readonly RequestFactoryInterface $requestFactory,
) {
}
```

Every argument is now a type the gateway's container knows, so the API is autowired and you declare
nothing. An API that still takes an array cannot be autowired: declare it in
`configureContainer()`, as [Services](services.md) shows.

Each `Action/Api/*Action.php` becomes a method on the API. A `CreateCheckoutAction` dispatched with
`new CreateCheckout($details)` becomes `$api->createCheckout($details)`.

Type-hint the PSR-18 `Psr\Http\Client\ClientInterface` and the PSR-17 factories, not
`Payum\Core\HttpClientInterface` or `Http\Message\MessageFactory`. Payum no longer installs
`php-http/message-factory`, so an API that needs it makes every application install it.

### Turn each action into a handler

A handler implements the interface for one command, takes its services through the constructor and
returns a result instead of throwing a reply. The capture action above becomes:

```php
<?php

namespace Acme\Payum\Handler;

use Acme\Payum\AcmeApi;
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

        if (isset($context->httpRequest()->getQueryParams()['cancelled'])) {
            $state['status'] = 'canceled';

            return CaptureResult::canceled();
        }

        if (! $state['checkout_id']) {
            $amount = $context->amount();

            $state->replace($this->api->createCheckout([
                'amount' => $amount?->getAmount(),
                'currency' => $amount?->getCurrency()->getCode(),
                'reference' => $context->payment()?->getNumber(),
                'return_url' => $context->token()?->getTargetUrl(),
            ]));

            return CaptureResult::pending(new Redirect($state['checkout_url']));
        }

        $state->replace($this->api->getCheckout($state['checkout_id']));

        return 'paid' === $state['status']
            ? CaptureResult::captured($state['charge_id'])
            : CaptureResult::pending();
    }
}
```

Most of the body carries over. `$context->state()` is the same `ArrayObject` the action unwrapped, and
Payum writes it back onto the payment. What changes:

- `supports()` and `assertSupports()` go. The handler interface decides which command reaches it.
- The `GetHttpRequest` sub-request becomes `$context->httpRequest()`.
- The `Convert` action goes. The handler reads the amount from `$context->amount()`, which works for a
  partial capture too, and anything else from `$context->payment()`.
- The reply is returned as a next action, and the status comes with the result, so the status action
  goes too.

Port the refund action to a `RefundHandler` the same way, returning a `RefundResult`. Capture still runs
twice for a redirect: once to send the customer away and once when the provider sends
them back to the token's URL. [Handlers](handlers.md) explains that flow and what `Context` offers.

### Port the notify action

A notify action becomes a handler implementing `NotifyHandlerInterface`. Its signature check moves into
`verify()`, which returns a `WebhookEvent`, and the rest moves into `handle()`. The `HttpResponse` the
action threw to answer the provider becomes an `Acknowledgement` on the `NotifyResult`. See
[Webhooks](webhooks.md).

### Move templates and extensions

- Templates: replace `payum.paths` with `DeclaresTemplates::templateNamespaces()`. An action that
  threw an `HttpResponse` with a rendered form returns a `RenderTemplate` next action naming the template.
  Never use `PayumCore` as your namespace: core reserves it. See [Templates](templates.md).
- Extensions: rewrite a `payum.extension.*` extension as a middleware, and list it in
  `DeclaresMiddleware::middleware()`. See [Middleware](middleware.md).

### Register it

The application registers the config object instead of an options array:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

Applications that still execute 1.x requests keep working against the ported gateway. The next section
shows what they get.

## What applications keep after you port

A gateway package releases on its own schedule, so an application can still execute 1.x requests at your
gateway after you port it:

```php
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;

try {
    $gateway->execute(new Capture($token));
} catch (HttpRedirect $reply) {
    header('Location: ' . $reply->getUrl());
}
```

Payum translates the request to the matching command, runs your handler and throws the reply a 1.x
caller expects:

| 1.x request | Answered by |
| :--- | :--- |
| `Capture`, `Authorize`, `Refund` | the matching handler |
| `Cancel`, `Sync` | the matching handler, for a payment or a payout |
| `Payout` | the payout handler |
| `Notify` | the notify handler |
| `GetHumanStatus`, `GetBinaryStatus` | the status Payum recorded on the payment |

| Next action | Reply thrown |
| :--- | :--- |
| `Redirect` | `HttpRedirect` |
| `PostRedirect` | `HttpPostRedirect` |
| `RenderTemplate` | `HttpResponse` with the rendered page |
| `Challenge` | `HttpPostRedirect` to the issuer, or `HttpRedirect` when it has no parameters |
| `Poll` | nothing |
| none | nothing |

Two of these lose detail, because 1.x has no reply for them. A `Challenge` loses its 3-D Secure protocol
version. A `Poll` loses `retryAfterSeconds`, and the caller goes to the after URL and reads a pending
status there. If your gateway needs either detail, its users have to act on the result of a command.

A status request is answered from the status Payum records, so the payment model has to implement
`Payum\Core\Model\StatusAwareInterface`. A payment that does not is marked unknown.

## Move one operation at a time

You do not have to port every action at once. A gateway can run handlers for the operations you have
ported and 1.x actions for the rest.

### Keep the unported actions beside the handlers

Implement `Payum\Core\Gateway\DeclaresActions` and list the actions you have not ported:

```php
<?php

namespace Acme\Payum;

use Acme\Payum\Action\RefundAction;
use Acme\Payum\Action\StatusAction;
use Acme\Payum\Handler\CaptureHandler;
use League\Uri\Uri;
use Payum\Core\Gateway\DeclaresActions;
use Payum\Core\Gateway\GatewayInterface;
use Payum\Core\Metadata\Logo;

final class AcmeGateway implements GatewayInterface, DeclaresActions
{
    public function name(): string
    {
        return 'Acme';
    }

    public function logo(): Logo
    {
        return Logo\Url::create('https://acme.test/logo.svg');
    }

    public function websiteUrl(): Uri
    {
        return Uri::new('https://acme.test');
    }

    public function configClass(): string
    {
        return AcmeConfig::class;
    }

    public function handlers(): array
    {
        return [CaptureHandler::class];
    }

    public function actions(): array
    {
        return [RefundAction::class, StatusAction::class];
    }
}
```

`CaptureCommand` and the 1.x `Capture` request go to the handler. The 1.x `Refund` request falls through
to the action. When the last action becomes a handler, remove the interface.

`actions()` returns class names, which Payum builds from the gateway's container. Give each listed
action its API through the constructor before you list it:

```php
public function __construct(private readonly AcmeApi $api)
{
}
```

An action that still implements `ApiAwareInterface` gets no API here, and every request that reaches the
actions then fails with "Cannot find right api for the action".

Two ordering rules decide who answers a request:

- Handlers are asked before actions. Core's own actions claim almost every request that carries a token,
  so asking them first would hide the handlers.
- Status requests are the exception: a status action wins. It reads the details of the operations that
  have not moved yet, which the recorded status does not know about.

Listing any action brings core's own actions and extensions along, because an action that executes
`GetHttpRequest` or `RenderTemplate` still expects an answer. A gateway that lists none gets no 1.x
machinery at all.

### Take commands for an unported action

A command only reaches handlers. In the gateway above, a `RefundCommand` is not supported, even though
`RefundAction` answers the `Refund` request. To take the command before you port the action, list the
matching handler from `Payum\Core\Legacy\Handler`, and keep the action:

```php
use Payum\Core\Legacy\Handler\RefundActionHandler;

public function handlers(): array
{
    return [CaptureHandler::class, RefundActionHandler::class];
}

public function actions(): array
{
    return [RefundAction::class, StatusAction::class];
}
```

`RefundCommand` now becomes the `Refund` request and runs through your actions. The reply the action
throws comes back as `$result->next`, and the status comes from your status action. There is one such
handler per command: `CaptureActionHandler`, `AuthorizeActionHandler`, `RefundActionHandler`,
`CancelActionHandler`, `SyncActionHandler`, `PayoutActionHandler` and `NotifyActionHandler`. Keep the
action in `actions()`, or nothing answers the request.

The result holds only a status and a next action, with no transaction ID, no amount and no failure. A
command that carries an amount, a reason or an idempotency key is refused with a `LogicException`,
because the 1.x request has nowhere to put it. Replace the handler with a real one when you port the
action.

### Add a handler to a 1.x factory

The other way round: your gateway is still a 1.x factory, and you want to write one operation as a
handler first. Register the handler as an action with `HandlerToActionAdapter`:

```php
<?php

namespace Acme\Payum;

use Acme\Payum\Action\CaptureAction;
use Acme\Payum\Action\ConvertPaymentAction;
use Acme\Payum\Action\StatusAction;
use Acme\Payum\Handler\RefundHandler;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\Legacy\HandlerToActionAdapter;

class AcmeGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'acme',
            'payum.factory_title' => 'Acme',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.refund' => static fn (ArrayObject $config) => new HandlerToActionAdapter(
                new RefundHandler(new AcmeApi((array) $config)),
            ),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ]);

        // payum.api as before
    }
}
```

A `Refund` request for a payment now runs `RefundHandler`, through the same middleware as on a gateway
built from handlers, and a 1.x caller catches the reply it decided. The closure builds the handler once
the options are known. The handler gets a full `Context`, with two limits on a gateway a factory builds:

- `$context->gateway()` knows only the gateway's name. Its logo, website and config class throw. Write
  the gateway class first if a handler needs them.
- A `Refund` request that carries only a details array, with no payment, is left to the actions. A
  handler always works on a payment.

When every action is a handler, replace the factory with a gateway class and drop the adapter.

### What core answers for an unported action

An unported action asks core for what it needs by executing a sub-request. Core still answers each one,
from the services the gateway's container holds, so you register nothing for them. A handler executes
none of these: it gets the same information directly.

| The action executes | Core answers it from | A handler uses instead |
| :--- | :--- | :--- |
| `GetHttpRequest` | the container's PSR-7 `ServerRequestInterface`, flattened into the 1.x arrays | `$context->httpRequest()` |
| `RenderTemplate` | `Payum\Core\Template\RendererInterface` | a `RenderTemplate` next action |
| `GetToken` | the token storage, by hash | `$context->token()` |
| `GetCurrency` | ISO 4217, then the `Money\Currencies` service | a `Money\Currencies` constructor argument |
| `ObtainCreditCard` | the framework integration, as in 1.x. Core does not answer it | |

`GetHttpRequest::$request` holds the query parameters merged with the parsed body, as `$_REQUEST` did.
Read `$query` when you mean the query string only.

## Replies your gateway defines for itself

A reply class of your own, or any reply core does not know, passes through unchanged. A 1.x caller
catches the object your action threw, and a caller acting on a result finds it in a
`Payum\Core\Legacy\LegacyReply` next action, as `$result->next->reply`. On the notify path, an
`HttpResponse` that a 1.x notify action throws becomes the `Acknowledgement` on the `NotifyResult` and
reaches the provider as the same response.

## What does not carry over

- An action whose `supports()` relied on a request Payum no longer creates for itself.
- Code that relied on the order in which core's own actions ran.
- Extensions on commands are one-way. An extension sees the command and can stop it by throwing, but it
  cannot replace the result, and `getAction()` on its context is always `null`, because a handler
  answered.
  Move code that changes the outcome into a middleware.
- Method overrides without types. Payum 2.x adds `: void` to `Generic::setModel()`,
  `GatewayAwareTrait::setGateway()` and `ApiAwareTrait::setApi()`, among others. A class that overrides
  one of them has to declare the same return type.

## Checklist

1. Write a config class for the options, with validation in the constructor.
2. Write the gateway class, listing your handlers.
3. Change the API's constructor to take the config, a PSR-18 client and PSR-17 factories.
4. Port each action to a handler, starting with capture. Use `DeclaresActions` and the
   `Legacy\Handler` classes for the ones you have not ported yet.
5. Port the notify action to `NotifyHandlerInterface`.
6. Replace `payum.paths` with `DeclaresTemplates` and extensions with middleware.
7. Remove the status action, the convert action, the API request actions and the factory.
8. Tell your users to switch to `registerGateway()` with your config class.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
