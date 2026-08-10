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

### Things that do not carry over

* Actions relying on `supports()` returning true for requests Payum no longer creates internally.
* Anything depending on the order in which Payum's own actions ran.
* Extensions, on the command path. Cross-cutting concerns move to middleware.

### Applications

An application usually changes one line. `Payum::capture()`, `Payum::done()` and the token flow are unchanged, and `Payum::capture()` dispatches a command when the gateway has a handler for it and the 1.x `Capture` request when it does not.

```php
-$reply = $gateway->execute(new Capture($token), true);
-if ($reply instanceof HttpRedirect) { header('Location: ' . $reply->getUrl()); }
+$result = $gateway->execute(CaptureCommand::forToken($token));
+if ($result->next instanceof Redirect) { header('Location: ' . $result->next->url); }
```
