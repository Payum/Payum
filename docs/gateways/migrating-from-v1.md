# Migrating a gateway from 1.x

Gateways written for 1.x keep working on 2.0 and are still supported. `Gateway::execute()` decides by what it is given: a command goes to a handler, anything else takes the action path. Nothing forces a migration, and you can move one operation at a time.

### What maps to what

| 1.x | 2.0 |
| :--- | :--- |
| `Payum\Core\Request\Capture` | `Payum\Core\Command\CaptureCommand` |
| `ActionInterface::execute($request)` | `CaptureHandlerInterface::handle($command, $context)` |
| `supports()` | Gone. The handler interface is the mapping |
| `throw new HttpRedirect($url)` | `return CaptureResult::pending(new Redirect($url))` |
| `$request->markCaptured()` | `return CaptureResult::captured($id)` |
| `ArrayObject::ensureArrayObject($request->getModel())` | `$context->state()` |
| `$this->gateway->execute(new GetHttpRequest())` | `$context->httpRequest()` |
| `$this->gateway->execute(new SomeApiRequest($details))` | `$this->api->someCall($details)` |
| `GatewayAwareTrait` + `$this->gateway->execute()` | `$context->execute()` |
| `GenericTokenFactoryAwareTrait` | `$context->tokens()` |
| `ApiAwareInterface` / `addApi()` | The api on the constructor |
| `GatewayFactory::populateConfig()` | A gateway class and a config object |
| `payum.action.*` container keys | `handlers()` on the gateway |
| `NotifyAction` | `NotifyHandlerInterface` — the signature check moves into `verify()`, and the `HttpResponse` the action threw becomes an `Acknowledgement` on the result |

Mirror the directory layout so the map stays obvious:

```
Action/CaptureAction.php   →  Handler/CaptureHandler.php
Action/Api/*.php           →  Api/AcmeApi.php  (methods, not actions)
AcmeGatewayFactory.php     →  AcmeGateway.php + Config/AcmeConfig.php
```

### 1. The config

Replace the option array in `populateConfig()` with a value object. Before:

```php
class AcmeGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'acme',
            'payum.factory_title' => 'Acme',
            'payum.action.capture' => new CaptureAction(),
        ]);

        $config['payum.default_options'] = ['secret_key' => '', 'sandbox' => false];
        $config['payum.required_options'] = ['secret_key'];

        $config['payum.api'] = function (ArrayObject $config) {
            $config->validateNotEmpty($config['payum.required_options']);

            return new AcmeApi((array) $config);
        };
    }
}
```

After:

```php
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

Required options become constructor arguments; validation moves into the constructor and now fails at boot.

### 2. The gateway

```php
final class AcmeGateway implements GatewayInterface
{
    public function name(): string        { return 'Acme'; }
    public function logo(): Logo          { return Logo\Path::create(__DIR__ . '/Resources/logo.svg'); }
    public function websiteUrl(): Uri     { return Uri::new('https://developer.acme.test'); }
    public function configClass(): string { return AcmeConfig::class; }

    public function handlers(): array     { return [CaptureHandler::class]; }
}
```

`payum.factory_title` becomes `name()`. The `payum.action.*` keys become `handlers()`.

### 3. The api

If your api already takes a PSR-18 client and its options, change it to take the config object and it will be autowired with no definition:

```php
-public function __construct(array $options, ClientInterface $client, RequestFactoryInterface $requestFactory)
+public function __construct(AcmeConfig $config, ClientInterface $client, RequestFactoryInterface $requestFactory)
```

Until then, declare it — an array has no type for the container to resolve. See [Services](services.md).

Every `Action/Api/*Action.php` becomes a method here. `SetExpressCheckoutAction` becomes `$api->setExpressCheckout()`.

### 4. The action becomes a handler

Most of the body survives untouched, which is why `$context->state()` is an `ArrayObject` and not a plain array. Before:

```php
class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if (isset($httpRequest->query['cancelled'])) {
            $details['CANCELLED'] = true;

            return;
        }

        if (! $details['checkout_id']) {
            $this->gateway->execute(new CreateCheckout($details));

            throw new HttpRedirect($details['checkout_url']);
        }

        $this->gateway->execute(new SyncCheckout($details));
    }

    public function supports($request)
    {
        return $request instanceof Capture && $request->getModel() instanceof ArrayAccess;
    }
}
```

After:

```php
final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(private readonly AcmeApi $api)
    {
    }

    public function handle(CaptureCommand $command, Context $context): CaptureResult
    {
        $state = $context->state();

        if (isset($context->httpRequest()->getQueryParams()['cancelled'])) {
            return CaptureResult::canceled();
        }

        if (! $state['checkout_id']) {
            $state->replace($this->api->createCheckout($state->toUnsafeArray()));

            return CaptureResult::pending(new Redirect($state['checkout_url']));
        }

        $state->replace($this->api->syncCheckout($state->toUnsafeArray()));

        return CaptureResult::captured($state['charge_id']);
    }
}
```

`supports()` and `assertSupports()` are gone — dispatch is by type. The reply is returned instead of thrown. The sub-requests became api calls.

### 5. Registering it

```php
-    ->addGateway('acme', ['factory' => 'acme', 'secret_key' => 'sk_…'])
+    ->registerGateway('acme', new AcmeConfig('sk_…'))
```

### 6. The status action

`GetHumanStatus` handling disappears. Status is on the result, and the named constructors set it:

```php
-if ($details['status'] === 'paid') { $request->markCaptured(); }
+return CaptureResult::captured($details['charge_id']);
```

### Moving one operation at a time

You do not have to port everything at once. A gateway can keep its factory and actions while a single handler moves across — `execute()` routes by argument type, so the two coexist in the same package.

### Dispatching a command at a gateway you have not ported

You get a `CommandNotSupportedException` saying so, rather than one that reads like a missing handler:

```
Gateway "legacy" handles no commands, so it cannot handle Payum\Core\Command\CaptureCommand.
It is built from actions: dispatch the matching Payum\Core\Request instead, or port it to handlers.
```

`$e->getSupportedCommands()` is empty and `$e->getGatewayClass()` is null for such a gateway, so code choosing between the two paths can branch on either.

### Things that do not carry over

* Actions relying on `supports()` returning true for requests Payum no longer creates internally.
* Anything depending on the order in which Payum's own actions ran.
* Extensions, on the command path. Cross-cutting concerns move to middleware.

### Applications do not have to change at all

A gateway package releases on its own schedule, so an application should not break because one of its dependencies moved to handlers. It does not: a 1.x request sent to a ported gateway is translated to the command that means the same thing, run through the handler, and answered the way 1.x expects.

```php
// unchanged, still works after the gateway ports
$gateway = $payum->getGateway($token->getGatewayName());

try {
    $gateway->execute(new Capture($token));
} catch (HttpRedirect $reply) {
    header('Location: ' . $reply->getUrl());
}
```

`Payum::capture()`, `Payum::done()`, the token factory and the token flow are all unchanged.

What translates:

| 1.x request | Answered by |
| :--- | :--- |
| `Capture`, `Authorize`, `Refund` | the matching handler |
| `Cancel`, `Sync` | the matching handler, for a payment or a payout |
| `Payout` | the payout handler |
| `GetHumanStatus`, `GetBinaryStatus` | the status recorded on the subject |
| `Notify` | the notify handler — see [Webhooks](webhooks.md) |

Two limits worth knowing:

- A status request is answered from the status Payum records, so it needs the subject to implement `Payum\Core\Model\StatusAwareInterface`. One that tracks nothing is marked unknown rather than guessed at.
- A handler returning `RenderTemplate`, `Challenge` or `Poll` has no 1.x reply to become, so those throw rather than report the payment finished when it is not. A gateway using them needs a caller that acts on a `Result`.

### Moving one operation at a time

A gateway does not have to port everything at once. List the actions you have not moved yet and they keep working beside the handlers you have:

```php
use Payum\Core\Gateway\DeclaresActions;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresActions
{
    public function handlers(): array
    {
        return [CaptureHandler::class];      // ported
    }

    public function actions(): array
    {
        return [RefundAction::class];        // not yet
    }
}
```

`Capture` goes to the handler. `Refund` falls through to the action. When the last action becomes a handler, drop the interface.

Declaring any action brings core's own actions and extensions along, since an action dispatching `GetHttpRequest` or `RenderTemplate` still expects an answer. A gateway that declares none gets a clean gateway with no 1.x machinery on it at all.

### What core answers for an action you have not ported

An unported action asks core for what it needs by dispatching a sub-request. It still gets an answer, but from the services 2.0 injects rather than from where 1.x found them. You register nothing for this.

| The action dispatches | Answered from |
| :--- | :--- |
| `GetHttpRequest` | the container's `Psr\Http\Message\ServerRequestInterface`, flattened back into the arrays the 1.x request carries |
| `RenderTemplate` | `Payum\Core\Template\RendererInterface` — the same renderer a handler's `RenderTemplate` result goes through |
| `GetToken` | the token storage |
| `GetCurrency` | ISO 4217 |
| `ObtainCreditCard` | your framework integration, as before. Core ships no answer for it |

Two consequences worth knowing:

- Whatever request the application registered is what the action sees. Register your framework's request once with `PayumBuilder::addGlobalService(ServerRequestInterface::class, $request)` and both an unported action and a handler reading `$context->httpRequest()` read the same thing — the action no longer reads `$_GET` and `$_REQUEST` behind your back.
- `GetHttpRequest::$request` is the query merged with the parsed body, the way `$_REQUEST` was. Read `$query` when you mean the query string only.

Two ordering rules worth knowing:

- **Handlers are asked before actions.** They have to be: a token is a `DetailsAggregateInterface`, so core's `ExecuteSameRequestWithModelDetailsAction` claims `Capture($token)`. Asking actions first would swallow almost every request before a handler saw it.
- **Except for status requests**, where an action wins. A gateway still holding a status action reads the details of whatever has not moved, which is more than the recorded status knows.

### Moving your own code across

When you are ready, the call site becomes:

```php
-$reply = $gateway->execute(new Capture($token), true);
-if ($reply instanceof HttpRedirect) { header('Location: ' . $reply->getUrl()); }
+$result = $gateway->execute(CaptureCommand::forToken($token));
+if ($result->next instanceof Redirect) { header('Location: ' . $result->next->url); }
```
