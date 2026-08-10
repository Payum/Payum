# Gateways

From 2.0 a gateway describes itself and handles typed **commands** that return a **result**. This chapter is the reference for that model: what to define, where to put it, and how it is wired.

_**Note**: gateways written for 1.x keep working and are still supported. `Gateway::execute()` decides by what you pass it — a command goes to a handler, anything else takes the action path. The_ [_migration guide_](migrating-from-v1.md) _shows how to move one across._

### The pieces

| Piece | What it is | Lifetime |
| :--- | :--- | :--- |
| `Payum\Core\Gateway\GatewayInterface` | Name, logo, website, which config it takes, which handlers it ships | One per gateway type |
| `Payum\Core\Config\GatewayConfig` | Credentials and switches, as a validated value object | One per configured gateway |
| Api | The only thing that talks to the PSP | One per configured gateway |
| `Payum\Core\Command\CommandInterface` | What the caller wants done. Immutable | One execution |
| `Payum\Core\Handler\HandlerInterface` | Answers one command | One per configured gateway |
| `Payum\Core\Handler\Context` | The payment, the token, the HTTP request, the PSP state | One execution |
| `Payum\Core\Result\Result` | Status, what must happen next, transaction id, failure | One execution |

### How they fit

```
$payum->getGateway('acme')->execute(CaptureCommand::forToken($token))
    │
    ▼
Gateway ── looks up the handler the gateway declared for CaptureCommand
    │
    ▼
CaptureHandler::handle($command, $context) ──► CaptureResult
    │                                              │
    ├── Api        (injected, talks to the PSP)    ├── status
    ├── Config     (injected, credentials)         ├── next: Redirect | RenderTemplate | …
    └── Context    (payment, token, PSP state)     └── transactionId, failure, raw
```

Two rules keep the boundaries honest:

* **Constructor** for anything that lives as long as the gateway — the api, the config, storages, a renderer, a logger.
* **Context** for anything that exists only for this execution — the payment, the token, the inbound HTTP request, the PSP state.

### Read next

* [Defining a gateway](defining-a-gateway.md) — the gateway class and its metadata
* [Configuration](configuration.md) — config objects and registering a gateway
* [Commands](commands.md) — what you can dispatch
* [Handlers](handlers.md) — answering a command, and the re-entrant capture
* [Results](results.md) — what comes back
* [Services](services.md) — the container, autowiring, and overriding
* [Middleware](middleware.md) — wrapping command execution
* [Migrating a gateway from 1.x](migrating-from-v1.md)
