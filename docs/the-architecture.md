# The Architecture

Every payment operation goes through a **gateway**. You dispatch a **command** saying what you want done, the gateway routes it to the **handler** that answers it, and you get back a **result** saying what happened and whether the customer still has something to do.

```php
<?php
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Result\NextAction\Redirect;

$result = $payum->getGateway('acme')->execute(CaptureCommand::forToken($token));

if ($result->next instanceof Redirect) {
    header('Location: ' . $result->next->url);
    exit;
}

$result->status;        // PaymentStatus::Captured
$result->transactionId;
```

_**Note**: gateways written for 1.x — actions, `supports()`, thrown replies — keep working and are still supported. `execute()` decides by what you pass it. That model is described at the_ [_bottom of this page_](the-architecture.md#the-1-x-model)_._

### Gateways

A gateway class is the definition: what it is called, which config it takes, which handlers it ships.

```php
<?php
final class AcmeGateway implements GatewayInterface
{
    public function name(): string        { return 'Acme Payments'; }
    public function logo(): Logo          { return Logo\Path::create(__DIR__ . '/Resources/logo.svg'); }
    public function websiteUrl(): Uri     { return Uri::new('https://developer.acme.test'); }
    public function configClass(): string { return AcmeConfig::class; }

    public function handlers(): array     { return [CaptureHandler::class, RefundHandler::class]; }
}
```

It takes no constructor arguments, so an application can list every installed gateway before any credentials exist — which is what an "add a payment method" screen needs. Credentials live in a config object:

```php
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->registerGateway('acme', new AcmeConfig('sk_live_…'))
    ->getPayum();
```

_**Link**:_ [_Defining a gateway_](gateways/defining-a-gateway.md) _and_ [_Configuration_](gateways/configuration.md)_._

### Commands and handlers

A command is immutable intent. A handler answers exactly one of them, and there is one handler interface per command so both sides of `handle()` are typed.

```php
<?php
final class RefundHandler implements RefundHandlerInterface
{
    public function __construct(private readonly AcmeApi $api)
    {
    }

    public function handle(RefundCommand $command, Context $context): RefundResult
    {
        $refund = $this->api->refund($context->state()['charge_id'], $context->amount()?->getAmount());

        return RefundResult::refunded($refund['id'], $refund['amount']);
    }
}
```

Listing the class in `handlers()` is the whole mapping — Payum reads which handler interface it implements and takes the command from that signature.

_**Link**:_ [_Commands_](gateways/commands.md) _and_ [_Handlers_](gateways/handlers.md)_._

### Results

A handler returns. Control flow lives in `$result->next`, which describes *intent* — never an HTTP response — so a bridge turns it into one and a JSON API can serialise it straight to a mobile client.

| Next action | Means |
| :--- | :--- |
| `Redirect`, `PostRedirect` | Send the customer to another URL |
| `RenderTemplate` | Show a page the gateway owns — a card form, a wallet button |
| `Challenge` | A step-up, 3-D Secure being the usual one |
| `Poll` | The PSP has not settled. Ask again later |
| `null` | Finished |

A declined card is a result, not an exception — `$result->failure` carries a portable reason plus the PSP's own code. Infrastructure faults, like an unreachable host or a rejected API key, still throw.

_**Link**:_ [_Results_](gateways/results.md)_._

### The context

Everything belonging to a single execution arrives on the context: the payment, the token, the inbound PSR-7 request, and the PSP state that has to survive between requests.

```php
$context->state();        // PSP state, an ArrayObject over the payment's details
$context->payment();
$context->token();
$context->httpRequest();  // PSR-7
$context->tokens();       // mint a notify or second-hop token
$context->execute($cmd);  // dispatch a sub-command
```

The dividing line is lifetime: **constructor** for anything that lives as long as the gateway, **context** for anything that exists only for this execution.

### Capture runs more than once

The PSP returns the customer to the capture token's own URL, so Payum dispatches the identical `CaptureCommand` again. The handler works out which pass it is on from the state it wrote:

```php
if ($state['checkout_id']) {
    // second pass: the customer has been and come back
    return CaptureResult::captured(…);
}

$state['checkout_id'] = $this->api->createCheckout(…)['id'];

return CaptureResult::pending(new Redirect($url));
```

Payum does not track phases. Some gateways need one pass, some three, and a 3-D Secure step-up can add one at any point.

### Dependency injection

Payum builds a [PHP-DI](https://php-di.org/) container per gateway, layered over one global container holding what every gateway shares — the PSR-18 client, the token storage, the token factories, the request verifier. That is why two gateways get the same HTTP client but their own, separately configured, api objects.

For most gateways nothing needs declaring: an api whose constructor takes only container entries is autowired. When autowiring cannot reach something, the gateway implements `ContainerConfiguration`.

_**Link**:_ [_Services_](gateways/services.md) _and the_ [_Dependency Injection_](di/README.md) _chapter._

### Persisting models

Before the customer is sent to the gateway you usually want the payment stored. That is handled by a [_storage_](../src/Payum/Core/Storage/StorageInterface.php). Payum writes the PSP state back onto the payment after a handler returns, and persists it when it was the one that loaded it — a payment you handed to a command yourself stays yours to persist.

[Doctrine](../src/Payum/Core/Bridge/Doctrine/Storage/DoctrineStorage.php), [Laminas Table Gateway](../src/Payum/Core/Bridge/Laminas/Storage/TableGatewayStorage.php) and [filesystem](../src/Payum/Core/Storage/FilesystemStorage.php) (tests only) storages are supported.

***

## The 1.x model

Still supported, and what every gateway in this repository currently uses. You create a [_request_](../src/Payum/Core/Request/Generic.php), implement an [_action_](../src/Payum/Core/Action/ActionInterface.php) that says it `supports()` it, and the gateway routes between them.

```php
<?php
use Payum\Core\Gateway;
use Payum\Core\Request\Capture;

$gateway = new Gateway;
$gateway->addAction(new CaptureAction);

$gateway->execute($capture = new Capture(['amount' => 100, 'currency' => 'USD']));

var_export($capture->getModel());
```

```php
<?php
class CaptureAction implements ActionInterface
{
    public function execute($request)
    {
        $model = $request->getModel();

        $model['status'] = 'success';
        $model['transaction_id'] = 'an_id';
    }

    public function supports($request)
    {
        return $request instanceof Capture;
    }
}
```

### Sub requests

An action delegates by creating a sub request. It must be *gateway aware* to do so.

```php
<?php
class FooAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        $this->gateway->execute(new BarRequest);
    }
}
```

The 2.0 equivalent is `$context->execute()`, and most sub-requests become plain method calls on the api.

### Replies

[_Replies_](../src/Payum/Core/Reply/Base.php) extend `Exception`, so they can be thrown from anywhere and caught at the top level.

```php
<?php
class FooAction implements ActionInterface
{
    public function execute($request)
    {
        throw new HttpRedirect('http://example.com/auth');
    }
}
```

```php
<?php
try {
    $gateway->execute(new FooRequest);
} catch (HttpRedirect $reply) {
    header('Location: ' . $reply->getUrl());
    exit;
}
```

The 2.0 equivalent is `return CaptureResult::pending(new Redirect(…))`, caught by a `match` rather than a `try`.

### Managing status

```php
<?php
class FooAction implements ActionInterface
{
    public function execute($request)
    {
        if ('success condition') {
            $request->markCaptured();
        } elseif ('pending condition') {
            $request->markPending();
        } else {
            $request->markUnknown();
        }
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface;
    }
}
```

The 2.0 equivalent is `PaymentStatus` on the result. Its backing values are these same strings, so stored data stays valid.

### Extensions

An [_extension_](../src/Payum/Core/Extension/ExtensionInterface.php) wraps every execution — checking permissions, logging, persisting models.

```php
<?php
class PermissionExtension implements ExtensionInterface
{
    public function onPreExecute(Context $context)
    {
        if (! in_array('ROLE_CUSTOMER', $context->getRequest()->getModel()->getRoles())) {
            throw new Exception('The user does not have the required roles.');
        }
    }
}
```

Extensions run on the action path. Cross-cutting concerns on the command path move to middleware.

### API aware actions

_**Note**: `ApiAwareInterface`, `ApiAwareTrait` and `addApi()` are deprecated since 2.0 and will be removed in 3.0. A handler receives its api through the constructor, and two api versions are simply two classes._

```php
<?php
class FooAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function execute($request)
    {
        $this->api;
    }
}
```

### Conclusion

Next: [Your order integration](your-order-integration.md), the [Gateways](gateways/README.md) chapter, or [Dependency Injection](di/README.md).

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
