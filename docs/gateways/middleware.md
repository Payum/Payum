# Gateway middleware

Middleware is code that runs around every command a gateway handles. Most middleware applies to every
gateway, so applications register it on the builder. [Middleware](../middleware.md) covers writing one,
registering it, ordering, and the middleware Payum registers itself. This page covers the one thing a
gateway author adds: middleware that only runs for your gateway.

## Declare middleware on the gateway

Implement `Payum\Core\Gateway\DeclaresMiddleware` on the gateway class and return the middleware it
needs:

```php
<?php

declare(strict_types=1);

namespace Acme\Payum;

use Acme\Payum\Middleware\AcmeAuditMiddleware;
use Payum\Core\Gateway\DeclaresMiddleware;
use Payum\Core\Gateway\GatewayInterface;

final class AcmeGateway implements GatewayInterface, DeclaresMiddleware
{
    public function middleware(): array
    {
        return [
            AcmeAuditMiddleware::class,
        ];
    }

    // name(), logo(), websiteUrl(), configClass() and handlers() as before
}
```

The entries are class names or container ids, built from the gateway's own container, so their
constructors are autowired like a handler's: they can take the gateway's API or config. Applications
using the gateway do not register them.

## Where it runs

A gateway's middleware sorts by priority together with the middleware the application registered and
the middleware Payum registers (see the table in [Middleware](../middleware.md#middleware-payum-registers)).
At the same priority, the gateway's middleware runs inside the application's, closer to the handler.

A middleware at the default priority of 0 runs inside `PersistStateMiddleware` and
`RecordPaymentStatusMiddleware`. It sees the result before Payum writes the handler's state and the
status onto the payment. When a handler throws, `PersistStateMiddleware` still writes the state, so a PSP
id the handler stored before failing survives, and `RecordPaymentStatusMiddleware` records nothing.

## When to use it

Use gateway middleware rarely. If the code would make sense for another gateway, it belongs in an
application middleware or in a package applications register. Work that belongs to talking to the PSP,
such as fetching an access token, belongs in the API class, where it can be cached: middleware would run
it for commands that never call the PSP.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
