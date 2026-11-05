# Gateways

A gateway connects Payum to one PSP (payment service provider). It describes itself, names the config
it needs, and ships one handler per operation the PSP supports. An application dispatches a command,
such as "capture this payment", and the gateway's handler answers with a result.

This section is the reference for gateway authors. To build one step by step first, follow
[Build a gateway](../develop-gateway-with-payum.md).

## The pieces

| Piece | What it is | How many |
| :--- | :--- | :--- |
| `Payum\Core\Gateway\GatewayInterface` | Name, logo, website, the config class it takes and the handlers it ships | One class per gateway |
| `Payum\Core\Config\GatewayConfig` | Credentials and switches, as a value object that validates itself | One per registered gateway |
| Api | Your own class, the only one that talks to the PSP | One per registered gateway |
| `Payum\Core\Command\CommandInterface` | What the caller wants done. Immutable | One per execution |
| `Payum\Core\Handler\HandlerInterface` | Answers one kind of command | One per registered gateway |
| `Payum\Core\Handler\Context` | The payment, the token, the HTTP request and the gateway's saved state | One per execution |
| `Payum\Core\Result\Result` | The new status, what the customer must do next, the transaction id, any failure | One per execution |

## How they fit together

```
$payum->getGateway('acme')->execute(CaptureCommand::forToken($token))
    │
    ▼
Gateway ── finds the handler the gateway listed for CaptureCommand
    │       and runs it inside the middleware
    ▼
CaptureHandler::handle($command, $context) ──► CaptureResult
    │                                              │
    ├── AcmeApi   (constructor, talks to the PSP)  ├── status
    ├── services  (constructor)                    ├── next: Redirect, RenderTemplate, ...
    └── Context   (payment, token, state)          └── transactionId, failure, raw
```

Where a handler gets something from depends on how long it lives:

* The constructor takes anything that lives as long as the gateway: the API class, a logger, the
  clock.
* The context carries anything that exists only for this execution: the payment, the token, the
  inbound HTTP request and the gateway's saved state.

## Pages in this section

* [Defining a gateway](defining-a-gateway.md): the gateway class, its metadata and its capabilities.
* [Configuration](configuration.md): the config class, and how an application registers a gateway.
* [Commands](commands.md): the operations a caller can ask for and what each carries.
* [Handlers](handlers.md): answering a command, the context, and the capture that runs twice.
* [Results](results.md): statuses, next actions and failures.
* [Templates](templates.md): showing the customer a page the gateway ships.
* [Services](services.md): what a handler can take in its constructor, and how to declare or replace
  a service.
* [Gateway middleware](middleware.md): middleware that only runs for your gateway. Application-wide middleware is on [Middleware](../middleware.md).
* [Webhooks](webhooks.md): receiving the messages a PSP sends when a payment changes.

To port a gateway written for Payum 1.x, see [Migrate a gateway from 1.x](migrating-from-v1.md).
