# Payum v2 — Command / Handler gateway architecture

> **Status:** agreed design, pass 1. Not committed. Supersedes the relevant parts of `V2_ARCHITECTURE.md`
> §6–§9 where the two disagree.
>
> **Scope:** the contracts and how they fit together, plus non-functional gateway stubs. The executor
> (`Gateway::execute()` dispatch), the middleware pipeline, and the BC adapters are later passes.

## 1. Decisions taken

| # | Decision | Rationale |
|---|---|---|
| 1 | Multi-phase capture keeps **v1 semantics**: one re-entrant command, handler infers its phase from persisted state | The return URL is the capture token's own URL. Core never owns the phase. BC is free. |
| 2 | **The gateway is an instance** implementing `Payum\Core\Gateway\GatewayInterface`, and it replaces the gateway factory. It must be constructible with **no required arguments** | Metadata has to be readable without credentials so an app can render an "add a payment method" screen — but that needs a bare constructor, not static methods. Static also could not implement `ContainerConfiguration`, which is what forced a duplicate services interface. Config-dependent metadata is given up, deliberately |
| 3 | **The handler declares its command**, via a per-command handler interface | PHP forbids narrowing a parameter type, so one shared `handle(CommandInterface)` cannot work. Reflecting the *interface* also means the implementation cannot drift |
| 4 | **Base `Result` + sealed `NextAction` + typed subclasses** | Uniform enough to log/persist/compose; specific enough that a `RefundResult` differs from a `CaptureResult` |
| 5 | **Middleware pipeline** for cross-cutting concerns, with a legacy-extension adapter | Deferred to its own PR. Specified in §8 |
| 6 | **Command / Handler naming**, not Request / Action | The v1 names collide in the same namespace during migration, and the contracts are inverted — same name, opposite meaning |
| 7 | Config is **not** injected into handlers | Config → Api → Handler. Handlers take their `Api`; DI makes the constructor free-form |
| 8 | State is `ArrayObject` over `$payment->getDetails()` for now | `SensitiveValue` unwrapping, reference semantics, null-for-missing-key, and `defaults()`/`validateNotEmpty()` make a v1 action body port as a copy-paste. Typed state is a later pass |
| 9 | `execute()` keeps forking on `instanceof CommandInterface` and returns `mixed` | One entry point. Narrowing comes from a PHPStan extension, not the signature. BC layer goes away in 3.0 |

## 2. The seven pieces

| Piece | Lifetime | Holds |
|---|---|---|
| `GatewayInterface` | one per gateway type, no required ctor args | metadata, handler list, config class |
| `GatewayConfig` (readonly VO) | per configured gateway | credentials, sandbox flag |
| `Api` | per configured gateway | config + PSR-18 client; talks to the PSP |
| `CommandInterface` (readonly) | one execution | the *intent* |
| `HandlerInterface` | per configured gateway | config/api/storages by constructor |
| `Context` | one execution | the *ambient* |
| `Result` | one execution | outcome |

The rule that keeps `Context` from becoming a service locator:

> **Constructor** = things that live as long as the gateway (config, api, storage, renderer, logger).
> **Context** = things that exist only for this execution (payment, token, HTTP request, persisted state).

## 3. Namespaces

```
Payum\Core\Gateway\GatewayInterface           the gateway (required methods)
Payum\Core\Gateway\DeclaresCapabilities       optional — nuance beyond what handlers() implies
Payum\Core\DI\ContainerConfiguration          optional — service definitions
Payum\Core\DI\CreatesGateway                  assembles a Gateway; normally only CoreGatewayFactory
Payum\Core\Gateway\Capability                 enum
Payum\Core\Metadata\Logo{,\Url,\Path,\Base64Encode}
Payum\Core\Config\GatewayConfig
Payum\Core\Command\{CommandInterface, CaptureCommand, AuthorizeCommand, RefundCommand}
Payum\Core\Handler\{HandlerInterface, CaptureHandlerInterface, …, Context}
Payum\Core\Result\{Result, CaptureResult, …, PaymentStatus, Failure, FailureReason, NextAction}
Payum\Core\Result\NextAction\{Redirect, PostRedirect, RenderTemplate, Challenge, Poll}
Payum\Core\Middleware\MiddlewareInterface     (later pass)
Payum\Core\Gateway                            executor, public API unchanged
```

`Result` lives outside `Handler\` because it is the public return type of `execute()`, not a handler internal.

`Payum\Core\Gateway\GatewayInterface` deliberately shares a short name with `Payum\Core\GatewayInterface`
(the executor). One practical hazard, since core lives in `Payum\Core`: **importing the former inside that
namespace silently shadows the latter**, which is resolved by namespace fallback rather than by a `use`.
`PayumBuilder` is the case in point — `addGateway()` type-hints the executor's interface, so the builder
refers to the gateway by FQN instead of importing it.

## 4. Handler contracts

PHP parameter types are contravariant — an implementation may widen, never narrow — so a single
`handle(CommandInterface $c)` on a shared interface cannot be specialised. One interface per command:

```php
interface HandlerInterface {}                       // marker, declares no handle()

interface CaptureHandlerInterface extends HandlerInterface
{
    public function handle(CaptureCommand $command, Context $context): CaptureResult;
}
```

Consequences, all of them wanted:

- Core reflects the **interface's** `handle()` first parameter to key the command → handler map.
  The implementation cannot lie about it.
- PHP enforces **one handler class per command** — `handle()` cannot be declared twice — so no
  `#[HandlesCommand]` attribute is needed at all.
- `handlers(): list<class-string<HandlerInterface>>` still types.
- Capability derivation: handler → interface → command → `Capability`.

## 5. A gateway, end to end

```php
final class StripeCheckoutGateway implements GatewayInterface, DeclaresCapabilities
{
    public function name(): string        { return 'Stripe Checkout'; }
    public function logo(): Logo          { return Logo\Url::create('https://…'); }
    public function websiteUrl(): Uri     { return Uri::new('https://stripe.com'); }
    public function configClass(): string { return StripeCheckoutConfig::class; }
    public function handlers(): array     { return [CaptureHandler::class]; }

    // only the nuance; Capture is derived from handlers()
    public function capabilities(): array { return [Capability::ThreeDSecure]; }
}

// and, when autowiring cannot reach something — Paypal's v1 Api takes an array:
final class ExpressCheckoutGateway implements GatewayInterface, ContainerConfiguration
{
    public function configureContainer(): array
    {
        return [Api::class => static fn (ContainerInterface $c): Api => new Api(
            $c->get(ExpressCheckoutConfig::class)->toArray(), …
        )];
    }
}

final class CaptureHandler implements CaptureHandlerInterface
{
    public function __construct(private readonly StripeApi $api) {}

    public function handle(CaptureCommand $command, Context $context): CaptureResult { … }
}
```

## 6. The two-phase capture

PayPal Express Checkout, the v1 sequence with the branch reading explicit accessors:

```php
public function handle(CaptureCommand $command, Context $context): CaptureResult
{
    $state = $context->state();                       // ArrayObject over $payment->getDetails()

    if ($context->httpRequest()->getQueryParams()['cancelled'] ?? false) {
        return CaptureResult::canceled();
    }

    if (! $state['TOKEN']) {                                        // ── phase 1
        $state['RETURNURL'] = $context->token()->getTargetUrl();    // the URL we are on
        $state['CANCELURL'] = $context->token()->getTargetUrl() . '?cancelled=1';
        $this->api->setExpressCheckout($state);

        return CaptureResult::pending(new Redirect($this->api->authorizeTokenUrl($state['TOKEN'])));
    }

    $this->api->getExpressCheckoutDetails($state);                  // ── phase 2

    if (! $state['PAYERID']) {
        return CaptureResult::pending(new Redirect(…));
    }

    $this->api->doExpressCheckoutPayment($state);

    return CaptureResult::captured($state['PAYMENTINFO_0_TRANSACTIONID']);
}
```

Request 1 → `next instanceof Redirect` → 302 to PayPal.
Customer pays → PayPal returns to `RETURNURL`, the same capture token URL.
Request 2 → same command, `TOKEN` set → finalize → `next === null` → 302 to `afterUrl`.

**What crosses the two requests is `$context->state()`.** That is also the honest answer to "results of
previous command calls": across requests it is persisted state; within one request, nested sub-commands
are on `$context->previous()`.

## 7. Container wiring

Layering is unchanged — `FallbackContainer($gatewayContainer, $globalContainer)`. The gateway container gains:

```php
[
    StripeCheckoutConfig::class    => $config,                       // concrete
    GatewayConfig::class           => fn ($c) => $c->get(StripeCheckoutConfig::class),
    StripeCheckoutGateway::class   => $gateway,                      // the instance
    GatewayInterface::class        => fn ($c) => $c->get(StripeCheckoutGateway::class),
    CaptureHandlerInterface::class => autowire(CaptureHandler::class),
    // ... plus $gateway->configureContainer() when it implements ContainerConfiguration
]
```

`StripeApi` needs **no definition**: its constructor takes `(StripeCheckoutConfig, ClientInterface)`, both
container entries, so PHP-DI autowires it. Entries above are generated by core from the gateway, so a
typical gateway declares nothing.

Services a gateway does need are declared by implementing `Payum\Core\DI\ContainerConfiguration`,
which the gateway can do precisely because it is an instance. Paypal is the worked example: its v1
`Nvp\Api` takes `array $options`, which has no type to resolve, so it declares the definition itself.
That need is a v1 artefact and disappears when the Api takes its config object.

**`ContainerConfiguration` was split.** It used to also declare `createGateway(ContainerInterface):
Gateway`, which a gateway has no business implementing — it should not know how an executor is built.
Assembly moved to `Payum\Core\DI\CreatesGateway`, leaving `ContainerConfiguration` as a single-method
"here are my definitions" contract. `PayumBuilder` calls a factory's `createGateway()` when it implements
`CreatesGateway` and falls back to `CoreGatewayFactory` otherwise, so assembly is uniform by default. In
practice only `CoreGatewayFactory` implements it; `PaypalExpressCheckoutGatewayFactory`'s version was
pure delegation and is gone.

Keeping `createGateway()` reachable also preserves ~15 `PayumBuilderGlobalContainerTest` cases that use a
factory's `createGateway()` as their observation point for `FallbackContainer` layering.

`ApiAwareInterface` and `UnsupportedApiException` are dead on this path: two API versions are two classes.

### `getActions()` / `getExtensions()`

Legacy-path only. Core's action list exists to serve v1 actions that dispatch information sub-requests
(`GetHttpRequestAction`, `RenderTemplateAction`, `GetTokenAction`, `GetCurrencyAction`,
`ExecuteSameRequestWithModelDetailsAction`). A v2 handler calls `$context->httpRequest()` instead.

```php
public function createGateway(ContainerInterface $container): Gateway
{
    $gateway = new Gateway();
    $gateway->setContainer($container);

    if ($container->has(GatewayInterface::class)) {
        $gateway->setHandlers($this->buildHandlerMap($container));
        return $gateway;                                     // no actions, no extensions, no Twig
    }

    if ($container->has('twig.env')) { TwigUtil::registerPaths(…); }
    foreach ($this->getActions() as $action)       { … }
    foreach ($this->getExtensions() as $extension) { … }

    return $gateway;
}
```

Both methods get `@deprecated … removed in 3.0`. Twig registration moves into the legacy branch — a
v2-only gateway stops paying for a Twig environment. `buildHandlerMap()` reflects a handful of interfaces
at boot; PHP-DI compiles, so it caches with the container.

### Entry point and static narrowing

`execute()` keeps forking on `instanceof CommandInterface` and returns `mixed`. Narrowing comes from a
PHPStan `DynamicMethodReturnTypeExtension` on `Gateway::execute()` that reads the command's
`@implements CommandInterface<TResult>`:

```php
/** @template TResult of Result */
interface CommandInterface { … }

/** @implements CommandInterface<CaptureResult> */
final readonly class CaptureCommand implements CommandInterface { … }
```

`$catchReply` is meaningless for commands (nothing is thrown) and already emits a deprecation.

`Payum::capture()` becomes a `match` on `$result->next` rather than `instanceof HttpRedirect` on a caught reply.

## 8. Middleware (later pass, specified here)

```php
interface MiddlewareInterface
{
    public function process(CommandInterface $command, Context $context, callable $next): Result;
}
```

Ordered from the container:

```
EndlessCycleDetector → Lock → LegacyExtension → LoadModel → PersistState → Log → handler
```

`LegacyExtensionMiddleware` runs the existing `ExtensionCollection` so v1 extensions keep working.
`PersistStateMiddleware` is `StorageExtension`'s job made explicit.

**Until that pass lands**, the command path runs **no** legacy extensions and core persists state inline in
`Gateway::execute()`'s `finally`. Half-wiring the existing extensions now would be worse than not running
them: they match on v1 request types, so against a `CaptureCommand` they would silently no-op.

## 9. BC, three directions

**A. v1 gateway, v2 caller.** No handler for the command → `LegacyCommandBridge` translates to the v1
request, runs the action pipeline, translates back. `HttpRedirect` → `NextAction\Redirect`,
`HttpPostRedirect` → `PostRedirect`, `HttpResponse` → `RenderedResponse`; status via `GetHumanStatus`.

**B. v2 gateway, v1 caller.** A v1 `Capture` arrives at a handler-only gateway → translate to
`CaptureCommand`, dispatch, write back onto the v1 request, and *throw* the matching reply, because that is
what a v1 caller's try/catch expects.

**C. Information requests keep working.** A v1 action calling `execute(new GetHttpRequest())` is served by a
core action delegating to the new PSR-7 provider. Cheapest high-value piece: every untouched third-party
gateway gets the new plumbing for free.

Two adapters make migration incremental rather than all-or-nothing:

- `ActionToHandlerAdapter` — a v1 action satisfies a `*HandlerInterface`.
- `HandlerToActionAdapter` — a handler becomes an `ActionInterface` whose `supports()` derives from the
  command class, so a half-migrated gateway still resolves through the legacy path.

## 10. Deferred, with the reason

| Topic | Status |
|---|---|
| Middleware pipeline | Own PR. §8 |
| Typed per-gateway state | Own pass. `Context::state(StripeState::class)` hydrates from the same details array, so both shapes coexist and a gateway opts in per handler |
| `moneyphp/money` | Not adopted in this pass. Commands carry `?int $amount` in minor units + currency code, matching `PaymentInterface`. Avoids a dependency decision inside a stub pass |
| Multi-tenancy | Open. Config is baked into the container at build time, so one gateway name = one merchant. Because config is a plain container entry, a later pass can swap it for a request-scoped factory without touching a handler or gateway |
| `psr/http-message` | `Context::httpRequest()` returns a PSR-7 `ServerRequestInterface`. Present in `vendor/` but **not a direct require** — must be added to `composer.json` before this ships |
| Webhooks / `NotifyCommand` | Not in pass 1. Verification and handling should be separate methods (`V2_ARCHITECTURE` §14) |
| Conformance test kit | Asserts declared capabilities match handlers, and that `GatewayConfig::getGatewayClass()` and `GatewayInterface::configClass()` agree — cheaper than validating at boot on every request |

## 11. Cleanup carried by this work

| File | Issue |
|---|---|
| `Core/Gateway.php` | `use Payum\Paypal\…\Handler` — Core must not reference a gateway package; `dd()` in `execute()` |
| `Core/CoreGatewayFactory.php` | `dd($container, debug_backtrace())` in the `twig.env` else-branch |
| `Core/Payum.php` | `use Command\CaptureCommand` — missing `Payum\Core\` prefix |
| `Core/GatewayInterface.php` | `use Command\CommandInterface` — same |
| `Core/Options/PaymentTokenOptions.php` | `namespace Options;` — should be `Payum\Core\Options` |
| `Paypal/…/Handler/Handler.php` | `dd()` in constructor and `handle()`; no return type |

## 12. Passes

1. **Contracts + stubs** (this pass) — gateway, command, handler, result, context contracts; illustrative
   Stripe Checkout and PayPal EC stubs. Nothing wired.
2. **Executor** — `Gateway::execute()` fork, `buildHandlerMap()`, `createGateway()` split,
   `PayumBuilder::registerGateway()`, inline state persistence, PHPStan extension.
3. **Middleware pipeline** + legacy extension adapter.
4. **BC layer** — the three directions and two adapters.
5. **Typed state**, then webhooks, then the conformance kit.
