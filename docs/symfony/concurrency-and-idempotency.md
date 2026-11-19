# Stop double captures

A customer who double-clicks Pay, or returns from the PSP (the payment service provider) while its
webhook reaches your server, sends two requests for the same payment at once. If both run the capture,
the customer can be charged twice. PayumBundle turns on Payum's locking with one line of configuration,
so only one request at a time works on a payment. It can also keep idempotency records, so that a
repeated operation gets its first result instead of reaching the PSP again.

## Turn on locking

Install [Symfony Lock](https://symfony.com/doc/current/components/lock.html):

```bash
composer require symfony/lock
```

Its Flex recipe configures a lock store in `config/packages/lock.yaml`. Without Flex, configure one
yourself:

```yaml
# config/packages/lock.yaml
framework:
    lock: '%env(LOCK_DSN)%'
```

Set `LOCK_DSN` to `flock` to keep the locks as files on one server. Then turn on locking in Payum:

```yaml
# config/packages/payum.yaml
payum:
    locking: true
```

The bundle registers Payum's `LockingMiddleware` with Symfony's `lock.factory`. Before a capture,
authorize, refund, cancel, payout or sync loads a stored payment, the middleware takes a lock on it. A
second request for the same payment waits until the first one finishes, then loads the payment with
everything the first one saved. Locking is off until you turn it on.

{% hint style="warning" %}
`flock` only works on one server. If your application runs on several, set `LOCK_DSN` to a store all of
them reach, such as `redis://redis-host:6379` or your database. Never point `flock` at a network file
system.
{% endhint %}

## Change how long a request waits

Set the options under `payum.locking`:

```yaml
# config/packages/payum.yaml
payum:
    locking:
        enabled: true
        timeout: 2.0
        ttl: 300.0
```

| Option | Default | What it changes |
| :--- | :--- | :--- |
| `enabled` | `false` | Turns locking on. `locking: true` is short for `enabled: true`. |
| `timeout` | `10.0` | Seconds a request waits for another one before Payum throws `ConcurrentOperationException`. |
| `ttl` | `300.0` | Seconds after which a store that expires locks, such as Redis, releases a lock whose process died. Keep it longer than your slowest PSP call. |
| `lock_factory` | `lock.factory` | The Symfony service that creates the locks. |

To keep Payum's locks in a store of their own, configure a named lock resource and pass its factory:

```yaml
# config/packages/lock.yaml
framework:
    lock:
        payum: '%env(PAYUM_LOCK_DSN)%'
```

```yaml
# config/packages/payum.yaml
payum:
    locking:
        enabled: true
        lock_factory: lock.payum.factory
```

Symfony names the factory of a resource `lock.<resource>.factory`.

## What the customer sees when a request waits too long

When a request waits longer than `timeout`, Payum throws `Payum\Core\Exception\ConcurrentOperationException`
and sends nothing to the PSP. The bundle's capture and authorize URLs catch it and redirect the customer to
the after URL of the token, where your done action shows the status. The other request is already
finishing the payment and invalidates the token when it is done.

The notify URL lets the exception through, so the PSP gets an error and sends its webhook again later. In a
controller of your own, catch the exception the same way:

```php
<?php
// src/Controller/PaymentController.php

namespace App\Controller;

use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\ConcurrentOperationException;
use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PaymentController extends AbstractController
{
    public function capture(Request $request, Payum $payum): Response
    {
        $token = $payum->getHttpRequestVerifier()->verify($request);

        try {
            $payum->getGateway($token->getGatewayName())->execute(CaptureCommand::forToken($token));
        } catch (ConcurrentOperationException) {
            return $this->redirect($token->getAfterUrl());
        }

        $payum->getHttpRequestVerifier()->invalidate($token);

        return $this->redirect($token->getAfterUrl());
    }
}
```

Dispatch the command on the token, as here, rather than loading the payment yourself. Payum then loads
the payment after it holds the lock, so the second request sees what the first one saved.

## Keep idempotency records

An idempotency key is a value you choose that names one operation, such as `order-1001-refund-1`. Payum
remembers the result of a command that carries one, and answers a repeat of the same key with that result
instead of asking the PSP again. By default it remembers keys only for the PHP request that ran the
command. To recognise a key in a later request, tell the bundle where to keep the records:

```yaml
# config/packages/payum.yaml
payum:
    idempotency:
        cache_pool: cache.app
```

Set exactly one of these options:

| Option | Keeps records in | Needs |
| :--- | :--- | :--- |
| `cache_pool` | The Symfony cache pool with this service id | Nothing beyond the pool |
| `dbal_connection` | A table, over the Doctrine DBAL connection with this service id, such as `doctrine.dbal.default_connection` | `doctrine/dbal` and the table |
| `service` | Your own service implementing `Payum\Core\Idempotency\IdempotencyStoreInterface` | |

`table` sets the table name for `dbal_connection`. It defaults to `payum_idempotency_record`. Create the
table with the migration Payum ships, as
[Concurrent requests and idempotency](../concurrency-and-idempotency.md#in-a-database-table) shows.

Use a pool that all your servers share and that a deploy does not clear. A pool that drops a record early
lets the next request with that key reach the PSP. Turn on locking as well: without it, two requests with
the same key that arrive at once both find no record, and both reach the PSP.

## Advanced: how locking and idempotency work

[Concurrent requests and idempotency](../concurrency-and-idempotency.md) explains what Payum locks, when it
does not lock, which results it replays, and when it throws `IdempotencyKeyReusedException`. That page
builds Payum by hand. The bundle does the same wiring from the configuration above.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
