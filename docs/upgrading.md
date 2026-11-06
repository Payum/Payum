# Upgrade from 1.x

Payum 2.x runs an application written for Payum 1.x. The gateways, the requests you execute, the replies
you catch, your extensions, your tokens and your stored payments keep working. Most of the 1.x API is
deprecated: it still works through every 2.x release and is removed in 3.0.

This page takes you through the upgrade in order. First you update your dependencies and fix the few
things that do break. Then you replace the deprecated calls one at a time, at your own pace. If you
maintain a gateway package, read [Migrate a gateway from 1.x](gateways/migrating-from-v1.md) as well.

## Update your requirements

Payum 2.x needs:

| Dependency | Supported versions |
| :--- | :--- |
| PHP | 8.1 or later |
| Symfony components | 6.4, 7 and 8 |
| Twig | 3 |
| Doctrine ORM | 2.14 or later, or 3 |
| Doctrine DBAL | 3 or 4 |
| `doctrine/persistence` | 2 or 3 |
| Doctrine MongoDB ODM with `ext-mongodb` 2 | ODM 2.11 or later |
| `php-di/php-di` | 7 |
| `psr/container` | 2 |
| `stripe/stripe-php`, for the Stripe gateways | 7.3 or later |

`payum/core` now also requires `moneyphp/money`, which needs the `bcmath` extension, and the PSR clock
and event dispatcher interfaces.

These packages and classes are gone:

- `payum/iso4217`. Currency data comes from `alcohol/iso4217` and `moneyphp/money`.
- The Propel and Propel2 storages. Use the [Doctrine or Laminas storages](storages.md).
- The `Zend` storage bridge. `Payum\Core\Bridge\Laminas\Storage\TableGatewayStorage` replaces it.
- `Payum\Core\Bridge\Guzzle\HttpClient` and `HttpClientFactory`. Payum uses a PSR-18 HTTP client.
- `php-http/message-factory` and `php-http/message`. Payum no longer requires them. A 1.x gateway whose
  API class type-hints `Http\Message\MessageFactory` or `Http\Message\StreamFactory` still needs them, so
  install them yourself if one does:

```bash
composer require php-http/message-factory php-http/message
```

Update Payum and the gateway packages together:

```bash
composer require payum/core:^2.0 --with-all-dependencies
```

Use `payum/payum:^2.0` instead if you installed the whole monorepo package.

## Fix what breaks

Your application keeps working after the update unless it uses one of the things below. Go through the
list before you deploy.

### Doctrine DBAL 4

The token mapping that Payum ships stores the token details in an `object` column, and DBAL 4 removed
that column type. On DBAL 4, register Payum's replacement before you create the entity manager:

```php
<?php

use Payum\Core\Bridge\Doctrine\Types\DbalObjectType;

DbalObjectType::register();
```

With DoctrineBundle, add it under `doctrine.dbal.types` instead:

```yaml
doctrine:
    dbal:
        types:
            object: Payum\Core\Bridge\Doctrine\Types\DbalObjectType
```

The type reads and writes the same serialized format, so you do not need a database migration. On DBAL
3 you do not need it at all. To store token details as JSON instead, see
[Storages](storages.md).

ORM 3 has no annotation mapping. If your own entities extend Payum's models and use annotations, map
them with attributes or XML. Payum's own mappings are XML and work on ORM 2 and 3.

### Signatures with new types

Payum 2.x adds parameter and return types to many methods. If one of your classes implements or extends
a Payum class and overrides one of these methods, give your method the same types, or PHP refuses to
load the class:

| Your class implements or extends | Typed methods |
| :--- | :--- |
| `StorageInterface` | `create(): object`, `support(object $model): bool`, `update(object $model): object`, `delete(object $model)` |
| `AbstractStorage` | the same, plus `delete(object $model): void`, `find($id): ?object`, `identify($model): IdentityInterface` and the `do*()` methods |
| `GatewayRegistryInterface` | `getGateway(string $name): GatewayInterface`, `getGateways(): array` |
| `StorageRegistryInterface` | `getStorage(string\|object $class): StorageInterface`, `getStorages(): array` |
| `GatewayFactoryRegistryInterface` | `getGatewayFactory(string $name): GatewayFactoryInterface`, `getGatewayFactories(): array` |
| `AbstractTokenFactory` | `generateUrl(string $path, array $parameters = []): string` |
| `Payment`, `Payout`, `Token`, `GatewayConfig`, `CreditCard` | every setter returns `void` |
| `Model\ArrayObject` | the `ArrayAccess` and `IteratorAggregate` methods |

A Doctrine entity that extends `Payum\Core\Model\Payment` and overrides no method needs no change.

### Payments and payouts record their status

`Payum\Core\Model\Payment` and `Payum\Core\Model\Payout` implement
`Payum\Core\Model\StatusAwareInterface`, so Payum records each operation's status on them. Their
Doctrine mappings gain a nullable `payum_status` column. Add it to every table you map from either
class, with a migration or `doctrine:schema:update`:

```sql
ALTER TABLE payment ADD payum_status VARCHAR(32) DEFAULT NULL;
```

A row stored before the column existed reads as `PaymentStatus::Unknown`. With MongoDB ODM the field is
`payumStatus` and needs no migration.

If your subclass already has a `getStatus()` or `setStatus()` method, give it the interface's
signatures, `getStatus(): PaymentStatus` and `setStatus(PaymentStatus $status): void`, or PHP refuses
to load the class. Your own method then replaces Payum's, and your own status property and column keep
working.

### A credit card keeps its raw values to itself

`Payum\Core\Model\CreditCard` holds the number, holder, security code and expiry date only in
`SensitiveValue`, so `serialize()` and `var_export()` no longer write them out. Its protected `$number`,
`$holder`, `$securityCode` and `$expireAt` properties are always `null` and go away in 3.0. If your
subclass or Doctrine mapping reads them, read the card through `getNumber()`, `getHolder()`,
`getSecurityCode()` and `getExpireAt()` instead, and never map them to a column.

### HTTP request verifiers

A verifier of your own must accept a `Psr\Http\Message\ServerRequestInterface` and check the token
against that request's URI. Extend `Payum\Core\Security\HttpRequestVerifier` to get that behaviour.

### Smaller breaks

- `Payum\Core\Action\GetCurrencyAction` takes an optional `Money\Currencies` as its constructor argument.
  If you passed it a `Payum\ISO4217\ISO4217`, pass nothing.
- `ContainerAwareCoreGatewayFactory`, `ContainerAwareRegistry` and `CoreGatewayFactoryBuilder` in
  `Payum\Core\Bridge\Symfony` no longer implement Symfony's `ContainerAwareInterface`, which Symfony 7
  removed. If you relied on the interface to get the container set, call `setContainer()` yourself, or
  add a `calls` entry to the service definition.
- A custom token storage keyed on `Payum\Core\Security\TokenInterface` should find tokens by their hash.
  Core's `GetToken` action looks a token up by hash, as `HttpRequestVerifier` always has.

## Check that it runs

With the requirements updated and the breaks fixed, your 1.x code runs on 2.x as it is:

- Every gateway in this repository (Stripe, PayPal, Klarna, Be2Bill, Payex, Sofort, Authorize.Net and
  Offline) still ships a 1.x gateway factory, and works as before.
- `PayumBuilder::addGateway()`, `$gateway->execute(new Capture($token))`, the replies you catch, your
  token factory calls and your capture, done and notify scripts all work.
- Your own extensions run, for 1.x requests and for the commands of a ported gateway.
- When a gateway package moves to 2.x, your 1.x requests keep working against it. Payum translates each
  one to the matching command and turns the result back into the reply you catch.
- Your database needs no migration. Stored tokens, payments and gateway configs read back unchanged.

You can deploy at this point and replace the deprecated code later.

### Seeing the deprecations

Payum reports each deprecated call with `trigger_deprecation()`, once per class rather than once per
call, and each message names the replacement. These notices are silenced unless an error handler
collects them, as the Symfony debug error handler and `symfony/phpunit-bridge` do. Some deprecations
are marked with `@deprecated` only, because core uses those classes itself. Static analysis and
Symfony's `DebugClassLoader` report the ones your own classes extend or implement.

Some notices come from inside a 1.x gateway: the factory itself, and requests such as `Convert` that its
actions execute. They go away when the gateway package ports to 2.x, and there is nothing in your
application to change for them.

## Replace the deprecated code

Each section below shows a 1.x call and what replaces it. They are independent of each other, so you can
do them in any order.

### Register gateways with a config object

A gateway written for 2.x comes with a config class that holds its credentials. Register it with
`registerGateway()` instead of `addGateway()`. Before:

```php
<?php

use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGateway('acme', [
        'factory' => 'acme',
        'api_key' => 'sk_test_123',
        'sandbox' => true,
    ])
    ->getPayum();
```

After:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

The config validates the credentials when you create it, so a missing key fails at boot rather than at
the first payment. The gateways in this repository have no config class yet. Keep `addGateway()` for them
until their package ships one, and accept its deprecation notice until then. See [Configure Payum](di/getting-started.md) for the builder's other
options.

### Create tokens with the token issuer

`Payum::getTokenFactory()` and its `create*Token()` methods are replaced by one `create()` method on
`Payum::getTokenIssuer()`, which takes a `TokenPurpose`. Before:

```php
$captureToken = $payum->getTokenFactory()->createCaptureToken('acme', $payment, 'done.php');
$notifyToken = $payum->getTokenFactory()->createNotifyToken('acme', $payment);
```

After:

```php
use Payum\Core\Security\TokenPurpose;

$captureToken = $payum->getTokenIssuer()->create(TokenPurpose::Capture, 'acme', $payment, 'done.php');
$notifyToken = $payum->getTokenIssuer()->create(TokenPurpose::Notify, 'acme', $payment);
```

`TokenPurpose` has `Authorize`, `Capture`, `Refund`, `Payout` and `Notify`. The paths you configured
with `PayumBuilder::setGenericTokenFactoryPaths()` still apply. To store a payment and create its capture
token in one call, use `$payum->prepare('acme', $payment, 'done.php')`.

Stored tokens, `TokenInterface` and your token mappings do not change. See
[Tokens and payment URLs](tokens.md) for the details.

### Verify tokens from a PSR-7 request

`HttpRequestVerifierInterface::verify()` takes a PSR-7 `ServerRequestInterface` and checks the token
against that request's URI. Passing `$_REQUEST` is deprecated, because the verifier then has to read the
URL from `$_SERVER`. Before:

```php
$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
```

After, with any PSR-7 implementation (Guzzle's here):

```php
use GuzzleHttp\Psr7\ServerRequest;

$token = $payum->getHttpRequestVerifier()->verify(ServerRequest::fromGlobals());
```

A Symfony HttpFoundation `Request` also works, and is checked against its own URI.

### Run payments with commands

In 1.x you executed a request and caught the reply the gateway threw. In 2.x you execute a command and
get back a result, which carries the payment status and the next thing the customer has to do. Before:

```php
<?php
// capture.php

use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;

include __DIR__ . '/config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

if ($reply = $gateway->execute(new Capture($token), true)) {
    if ($reply instanceof HttpRedirect) {
        header('Location: ' . $reply->getUrl());
        exit;
    }

    throw new LogicException('Unsupported reply', 0, $reply);
}

$payum->getHttpRequestVerifier()->invalidate($token);

header('Location: ' . $token->getAfterUrl());
```

After:

```php
<?php
// capture.php

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Result\NextAction\Redirect;
use GuzzleHttp\Psr7\ServerRequest;

include __DIR__ . '/config.php';

$token = $payum->getHttpRequestVerifier()->verify(ServerRequest::fromGlobals());
$gateway = $payum->getGateway($token->getGatewayName());

$result = $gateway->execute(CaptureCommand::forToken($token));

if ($result->next instanceof Redirect) {
    header('Location: ' . $result->next->url);
    exit;
}

$payum->getHttpRequestVerifier()->invalidate($token);

header('Location: ' . $token->getAfterUrl());
```

This script needs a gateway built from handlers. For a 1.x gateway, wrap it first, as the next section
shows. A redirect is one of several next actions, and a decline is a `Failure` on the result rather than
an exception. [Payment operations](payment-operations.md) covers each of them, and the `Payum::capture()`
shortcut that turns the result into a Symfony HttpFoundation response for you.

The same applies to the other operations. Each 1.x request has a command:

| 1.x request | Command |
| :--- | :--- |
| `Capture` | `CaptureCommand` |
| `Authorize` | `AuthorizeCommand` |
| `Refund` | `RefundCommand` |
| `Cancel` | `CancelCommand` |
| `Payout` | `PayoutCommand` |
| `Sync` | `SyncCommand` |
| `Notify` | `NotifyCommand` |

The commands live in `Payum\Core\Command`. Passing the second `$catchReply` argument to
`Gateway::execute()` is deprecated along with the requests.

### Run a 1.x gateway with commands

Commands reach a gateway's handlers, and a gateway written for 1.x has none. Executing a command at it
fails with a message that says so:

```text
Gateway "paypal" handles no commands, so it cannot handle Payum\Core\Command\CaptureCommand. It is built
from actions: wrap it with Payum\Core\Legacy\LegacyGatewayAdapter::wrap() to dispatch commands at it, or
port it to handlers.
```

Wrap the gateway with `LegacyGatewayAdapter` to execute commands at it anyway:

```php
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Legacy\LegacyGatewayAdapter;

$gateway = LegacyGatewayAdapter::wrap($payum->getGateway($token->getGatewayName()));

$result = $gateway->execute(CaptureCommand::forToken($token));
```

The adapter turns each command into the matching 1.x request and the reply the gateway throws into the
result's next action. It reads the status by asking the gateway's own status action. Redirects work as
before: the customer comes back to the capture URL and the same command finishes the payment. Anything
that is not a command passes straight through, so you can keep the wrapper in front of a gateway you
still execute 1.x requests at.

A 1.x gateway cannot report everything a result holds:

- `$result->status` and `$result->isFailed()` work. `$result->transactionId`, the amounts and
  `$result->failure` are always `null`. Read anything else from the payment's details.
- A command that carries an amount, a reason or an idempotency key is refused with a `LogicException`,
  because the 1.x request has nowhere to put it. A partial refund run as a full refund would cost you
  money.
- A command the gateway has no action for throws a `CommandNotSupportedException`.
- A 1.x action that renders its own page, such as a card form, throws an `HttpResponse`. It reaches you
  as a `Payum\Core\Legacy\LegacyReply` next action holding that reply, for you to send:

```php
use Payum\Core\Legacy\LegacyReply;
use Payum\Core\Reply\HttpResponse;

if ($result->next instanceof LegacyReply && $result->next->reply instanceof HttpResponse) {
    http_response_code($result->next->reply->getStatusCode());
    echo $result->next->reply->getContent();
    exit;
}
```

`$gateway->capabilities()` lists the operations the gateway has actions for. Treat it as a hint for an
admin screen: an action that claims refunds can still refuse one.

### Read the payment status from the result

`GetHumanStatus` and `GetBinaryStatus` are deprecated. A command's result carries a
`Payum\Core\Result\PaymentStatus` in `$result->status`, and a payment model that implements
`Payum\Core\Model\StatusAwareInterface` has its status written onto it after every command. Before:

```php
<?php
// done.php

use Payum\Core\Request\GetHumanStatus;

include __DIR__ . '/config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

$gateway->execute($status = new GetHumanStatus($token));
$payment = $status->getFirstModel();

$payum->getHttpRequestVerifier()->invalidate($token);

if ($status->isCaptured()) {
    // ...
}
```

After, for a payment model that implements `StatusAwareInterface`, as Payum's own `Payment` does:

```php
<?php
// done.php

use Payum\Core\Model\PaymentStatuses;
use Payum\Core\Result\PaymentStatus;

include __DIR__ . '/config.php';

$payment = $payum->done();

if (PaymentStatuses::of($payment) === PaymentStatus::Captured) {
    // ...
}
```

`Payum::done()` verifies the token, deletes it and returns the payment. Like `capture()` and `notify()`,
it reads the request from the globals through Symfony HttpFoundation, so it needs
`symfony/http-foundation`. See
[Payment status](payment-status.md) for adding the status to your model.

Only gateways built from handlers record the status on your model. For a 1.x gateway, keep the
`GetHumanStatus` request, or read `$result->status` from the command you ran through
`LegacyGatewayAdapter`.

`PaymentStatus` has four statuses 1.x did not: `PartiallyCaptured`, `PartiallyRefunded`, `Disputed` and
`ChargedBack`. A `GetHumanStatus` answered from a recorded status reads the partial ones as captured and
refunded, and the other two as unknown. Give any `match` over `PaymentStatus` a `default` arm, because
more statuses can be added.

### Handle notifications with `Payum::notify()`

A notification is the request a payment provider sends to your site when a payment changes. Before:

```php
<?php
// notify.php

use Payum\Core\Request\Notify;

include __DIR__ . '/config.php';

$token = $payum->getHttpRequestVerifier()->verify($_REQUEST);
$gateway = $payum->getGateway($token->getGatewayName());

$gateway->execute(new Notify($token));
```

After:

```php
<?php
// notify.php

include __DIR__ . '/config.php';

$payum->notify()->send();
```

`Payum::notify()` executes a `NotifyCommand` at a gateway built from handlers and falls back to the
1.x `Notify` request for a 1.x gateway. It returns the response the provider expects: the gateway's own
answer, 204 when it gives none, and 400 when the message fails verification. See
[Payment notifications](instant-payment-notification.md).

### Use money objects for amounts

Payum 2.x expresses amounts as `Money\Money` objects from `moneyphp/money`. Nothing is removed, so your
integer amounts keep working:

- `Payum\Core\Model\Payment` and `Payout` have `getMoney()` and `setMoney()`, mapped onto the
  `totalAmount` and `currencyCode` they already store. Your database and mappings do not change.
- `CaptureCommand`, `AuthorizeCommand` and `RefundCommand` take a `Money` or an integer in minor units.
- Results report `capturedMoney`, `authorizedMoney`, `refundedMoney` and `paidOutMoney` next to the
  integer amounts.

A payment model of your own opts in by implementing `Payum\Core\Model\MoneyAwareInterface`. To support a
currency that ISO 4217 does not list, register your own `Money\Currencies` with
`PayumBuilder::addGlobalService()`. See [Money and currencies](money.md).

### Stop using a `Convert` action of your own

`Convert` has no replacement: a gateway built from handlers builds the provider's payload from the
payment itself. If you registered your own `Convert` action to add fields such as an invoice number,
write those fields onto the payment's details before you execute the command:

```php
$payment->setDetails(array_replace($payment->getDetails(), [
    'invoice_number' => 'INV-1001',
]));
```

A gateway you register with `registerGateway()` never runs a `payum.action.*` entry you add with
`addCoreGatewayFactoryConfig()` or `addGlobalService()`. `getPayum()` raises a deprecation notice that
names the gateway and each ignored entry, so check your deprecation log after you switch a gateway over.

### Remove core actions from your gateway config

Core answers `GetHttpRequest` and `RenderTemplate` for every 1.x gateway on its own. If your config
registers one of these deprecated actions, remove the entry rather than replacing it:

- `Payum\Core\Bridge\PlainPhp\Action\GetHttpRequestAction`
- `Payum\Core\Bridge\Twig\Action\RenderTemplateAction`

Core's `GetHttpRequest` reads a PSR-7 request instead of `$_GET`, `$_REQUEST` and `php://input`. For a
gateway you register with `registerGateway()`, register your framework's request once with
`PayumBuilder::addGlobalService(ServerRequestInterface::class, $request)`. A gateway you register with
`addGateway()` does not see global services. Give it the request through the core gateway config
instead:

```php
use Psr\Http\Message\ServerRequestInterface;

$builder->addCoreGatewayFactoryConfig([
    ServerRequestInterface::class => $serverRequest,
]);
```

In the same way, `PayumBuilder::setLayout()` applies to gateways registered with `registerGateway()`. For
a gateway registered with `addGateway()`, keep the `payum.template.layout` option in its config.

### Replace extensions that core ships

Your own extensions keep running, for 1.x requests and for commands. On a command an extension sees the
command go in and can stop it by throwing, but it cannot change the result. Core's own extensions that a
middleware now replaces are deprecated:

| Deprecated | Replacement |
| :--- | :--- |
| `Payum\Core\Extension\EndlessCycleDetectorExtension` | `Payum\Core\Middleware\EndlessCycleDetectorMiddleware`, which core registers itself |
| `Payum\Core\Extension\PrependExtensionInterface` | `Payum\Core\Middleware\HasPriority` on a middleware |
| `Payum\Core\Extension\GenericTokenFactoryExtension` | `Context::tokens()` in a handler |

Move your own extensions to middleware as you touch them. Middleware runs around every command on every
gateway, and unlike an extension it can change the result. An extension's methods map onto one
`process()` method:

| Extension | Middleware |
| :--- | :--- |
| `onPreExecute()` / `onExecute()` | Code before `$next($command, $context)` |
| `onPostExecute()` | Code after `$next()`, with the result in hand |
| `$context->setReply()` | Return a different result |
| `$context->getException()` | `try`/`catch` around `$next()` |
| `$gateway->addExtension()` or `payum.extension.*` | `PayumBuilder::addMiddleware()` |

See [Middleware](middleware.md) for writing and registering one.

### Listen for PSR-14 events instead of the Symfony extension

`Payum\Core\Bridge\Symfony\Extension\EventDispatcherExtension` is deprecated. Payum announces what
happens to a payment as PSR-14 events instead. Register your dispatcher once and every gateway uses it:

```php
use Psr\EventDispatcher\EventDispatcherInterface;

$builder->addGlobalService(EventDispatcherInterface::class, $dispatcher);
```

Listen for `Payum\Core\Event\StatusChanged`, for example, to fulfil an order when its payment is
captured. Events are dispatched for commands only, so a 1.x gateway announces nothing. Keep the
extension for those gateways until they port. See [Events](events.md).

### Use PSR-18 and PSR-17 for HTTP

The `httplug.*` config keys and Payum's own HTTP client interfaces are deprecated:

| Deprecated | Replacement |
| :--- | :--- |
| `httplug.client` | `payum.http_client`, a `Psr\Http\Client\ClientInterface` |
| `httplug.message_factory` | `payum.http_message_factory`, a `Psr\Http\Message\RequestFactoryInterface` |
| `httplug.stream_factory` | `payum.http_stream_factory`, a `Psr\Http\Message\StreamFactoryInterface` |
| `Payum\Core\HttpClientInterface` | `Psr\Http\Client\ClientInterface` |
| `Payum\Core\Bridge\Httplug\HttplugClient` | a PSR-18 client, for example from `Http\Discovery\Psr18ClientDiscovery::find()` |
| `HttplugClient::send()` | `sendRequest()` |

A `Payum\Core\HttpClientInterface` you still set as `payum.http_client` keeps working: Payum wraps it in
a PSR-18 adapter. Change your type-hints from `HttpClientInterface` and `HttplugClient` to
`Psr\Http\Client\ClientInterface`. To give every gateway registered with `registerGateway()` the same
client, register it with `PayumBuilder::addGlobalService(ClientInterface::class, $client)`.

### Gateway configs stored in a database

If you load gateway configs from a database with `PayumBuilder::setGatewayConfigStorage()`, nothing
changes. Two details are deprecated:

- `GatewayConfigInterface::getFactoryName()` and `setFactoryName()`. Put the factory name in the config
  array instead, under the `factory` key.
- The fallback from a `DynamicRegistry` to the registry you pass it. In 3.0 a `DynamicRegistry` returns
  only the gateways stored in the database. If you build one yourself, switch the fallback off and wrap
  it in a `Payum\Core\Registry\FallbackRegistry`, the way the builder does. Remove the
  `setBackwardCompatibility()` call when you move to 3.0.

```php
use Payum\Core\Registry\DynamicRegistry;
use Payum\Core\Registry\FallbackRegistry;

$dynamicRegistry = new DynamicRegistry($gatewayConfigStorage, $registry);
$dynamicRegistry->setBackwardCompatibility(false);

$registry = new FallbackRegistry($dynamicRegistry, $registry);
```

See [Store gateway configs in a database](configure-gateway-in-backend.md).

### The Symfony bridge

The whole `Payum\Core\Bridge\Symfony` namespace is deprecated and moves to `payum/payum-bundle`. Each
class has a counterpart at the same relative path under `Payum\Bundle\PayumBundle`, and each deprecation
message names it. The `CreditCardDate` validator constraint also takes named arguments now:
`new CreditCardDate(min: 'today')`.

### Other deprecations

| Deprecated | Replacement |
| :--- | :--- |
| `Payum\Core\Model\Identificator` | `Payum\Core\Model\Identity` |
| `CreditCardInterface::secure()` | nothing: the setters already wrap sensitive values |
| `Payum\Core\Bridge\Twig\TwigFactory` | `Payum\Core\Bridge\Twig\TwigUtil::registerPaths()` on your own Twig environment |
| `SimpleRegistry::setAddStorageExtensions()` | nothing: gateways built from handlers persist through middleware |
| The Omnipay v2 factories registered by `PayumBuilder` | `payum/omnipay-v3-bridge` |
| `ApiAwareInterface`, `ApiAwareTrait`, `payum.api.*` keys | the API as a constructor argument |
| `GatewayAwareAction` | `GatewayAwareInterface` with `GatewayAwareTrait` |
| `Payum\Core\Bridge\Spl\ArrayObject` as a container service | `Psr\Container\ContainerInterface` |
| 1.x gateway factories, `GatewayFactory::populateConfig()`, `payum.action.*` and `payum.extension.*` | a gateway class, see [Migrate a gateway from 1.x](gateways/migrating-from-v1.md) |

`Payum\Core\Request\Generic`, `Payum\Core\Action\ActionInterface`, `ExtensionInterface`,
`ExtensionCollection` and `Extension\Context` are not deprecated.

## Optional database changes

None of these are required. Each one is a choice you can make after the upgrade.

- **Token details as JSON.** Payum stores token details as serialized PHP. You can move them to a JSON
  column with a Doctrine migration and a metadata listener that Payum ships. See [Storages](storages.md).
- **Payment status on your model.** Implement `StatusAwareInterface` on your payment entity and add a
  column for it. See [Payment status](payment-status.md).

## Behaviour changes you may notice

These changes are not deprecations, but code that depends on the old behaviour can see a difference:

- The PayPal Express Checkout, Pro Checkout and Masspay, Sofort and Authorize.Net AIM gateways write the
  amount into the payment details as an exact decimal string, such as `'12.34'`, where 1.x wrote a float.
  The value sent to the provider is the same for a two-decimal currency and now correct for the others.
  Cast the value if your code does arithmetic on those details, or read the amount from the payment.
- Core's `GetHttpRequest` fills `$headers` from the request. Its `$request` holds the query parameters
  merged with the parsed body, as `$_REQUEST` did.
- The PSR-7 request Payum builds from the globals reads its body from `php://input`, so a gateway can
  check a signature over the raw payload.
- A payment that arrived on a token is saved whenever a command changes its status, even when the handler
  wrote no PSP state. On a gateway that still answers some commands with 1.x actions, such a command can
  save the payment twice: once from the action and once to keep the new status.
- Core's `GetToken` action now finds tokens. It used to look them up by an identity that matched nothing
  in the storages Payum ships.
- A currency that ISO 4217 does not list now works with 1.x gateways too, when your `Money\Currencies`
  service knows it.
- The current time comes from a PSR-20 clock that you can replace with
  `PayumBuilder::addGlobalService(ClockInterface::class, $clock)`. `Payum\Sofort\Action\StatusAction`
  takes the clock as its first constructor argument and still works with none.
- `PaymentInterface::getTotalAmount()` and `getCurrencyCode()` are documented as nullable. Static analysis
  may report new possible nulls.

## Checklist

1. Update to PHP 8.1 and the dependency versions in [the requirements](#update-your-requirements).
2. Install `php-http/message-factory` and `php-http/message` if a 1.x gateway's API needs them.
3. On DBAL 4, register `DbalObjectType`.
4. Add the new types to any class that overrides a Payum method.
5. Run your tests with deprecation notices collected.
6. Replace `getTokenFactory()` with `getTokenIssuer()`.
7. Pass a PSR-7 request to the HTTP request verifier.
8. Remove `GetHttpRequestAction` and `RenderTemplateAction` from your gateway configs.
9. Move from requests and replies to commands and results, with `LegacyGatewayAdapter` for 1.x gateways.
10. Move from `GetHumanStatus` to `$result->status` or `StatusAwareInterface`.
11. Replace the `httplug.*` keys, `ApiAwareInterface` and core's deprecated extensions.
12. Switch each gateway to `registerGateway()` once its package ships a config class.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
