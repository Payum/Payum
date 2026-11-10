# Concurrent requests and idempotency

Two requests for the same payment often arrive at the same time. A customer double-clicks Pay, or
returns from the PSP (the payment service provider) while the PSP's webhook about that payment reaches
your server. If both requests run the capture, the customer can be charged twice. Payum's
`LockingMiddleware` lets only one command at a time work on a payment, and the second one waits for the
first to finish. It is not turned on by default: install Symfony Lock and register it. An idempotency
key on the command lets Payum recognise a repeat of the same operation and answer it with the first
result.

## Turn on locking

Install [Symfony Lock](https://symfony.com/doc/current/components/lock.html):

```bash
composer require symfony/lock
```

Then register a lock factory and the middleware on the builder:

```php
<?php

use Payum\Core\Middleware\LockingMiddleware;
use Payum\Core\PayumBuilder;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGlobalService(LockFactory::class, new LockFactory(new FlockStore()))
    ->addMiddleware(LockingMiddleware::class)
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

`FlockStore` keeps the locks as files in the system's temporary directory. That works for every PHP
process on one server, such as all the PHP-FPM workers behind one web server. Every gateway uses the
same factory, so two gateways working on the same payment also wait for each other.

## What gets locked

Before a command loads or changes a stored payment or payout, the middleware takes a lock on it. This
applies to every capture, authorize, refund, cancel, payout and sync. It also applies to a notify when
the notify arrived on a token or names a payment. A second command for the same payment waits for the
lock. When the first command finishes, the second one loads the payment with everything the first one
saved.

A command does not lock when there is nothing it could race with:

- the payment was never saved, so no other request can know about it
- the notify names no payment yet, because the gateway only finds the payment by reading the message

## Run on more than one server

File locks only work on one server. If your application runs on several servers, use a store that all of
them reach, such as Redis or your database:

```php
<?php

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;

$redis = new Redis();
$redis->connect('127.0.0.1');

$builder->addGlobalService(LockFactory::class, new LockFactory(new RedisStore($redis)));
```

Symfony Lock also has stores for PDO, Doctrine DBAL and Memcached. Any of them works.

{% hint style="warning" %}
Never point `FlockStore` at a directory on NFS or another network file system. File locks there are not
reliable, and two servers can capture the same payment at once.
{% endhint %}

A store such as Redis releases a lock after five minutes even if the process holding it died, so a
crashed request cannot hold a payment forever. A file lock is released when the process exits.

## Handle a command that waited too long

A command waits up to 10 seconds for the lock. If the other command is still running after that, Payum
throws `Payum\Core\Exception\ConcurrentOperationException`. Nothing was sent to the PSP, so the
command is safe to retry.

On a capture URL, the request that waited usually has nothing left to do: the other request is already
finishing the payment. Send the customer on to the page that shows the outcome:

```php
<?php
// capture.php

use GuzzleHttp\Psr7\ServerRequest;
use Payum\Core\Command\CaptureCommand;
use Payum\Core\Exception\ConcurrentOperationException;

require __DIR__.'/config.php';

$token = $payum->getHttpRequestVerifier()->verify(ServerRequest::fromGlobals());

try {
    $result = $payum->getGateway($token->getGatewayName())->execute(CaptureCommand::forToken($token));
} catch (ConcurrentOperationException) {
    header('Location: '.$token->getAfterUrl());

    exit;
}

respond($payum, $result, $token);
```

`respond()` is the helper from [Payment operations](payment-operations.md), which shows the rest of a
capture endpoint. `$payum->capture()` does not catch the exception either, so wrap that call the same
way.

For a webhook, let the exception through, or answer with an error status. The PSP sends the webhook again
later.

## Change how long a command waits

To change the middleware's settings, register a closure that builds it. The closure takes the lock factory
and the storages from the container. Then register the middleware with `addMiddleware()` as before:

```php
<?php

use Payum\Core\Middleware\LockingMiddleware;
use Payum\Core\Registry\StorageRegistryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Lock\LockFactory;

$builder->addGlobalService(LockingMiddleware::class, static fn (ContainerInterface $c): LockingMiddleware => new LockingMiddleware(
    $c->get(LockFactory::class),
    $c->get(StorageRegistryInterface::class),
    timeout: 2.0,
));
$builder->addMiddleware(LockingMiddleware::class);
```

| Argument | Default | What it changes |
| :--- | :--- | :--- |
| `timeout` | `10.0` | Seconds a command waits for another one before it throws `ConcurrentOperationException` |
| `ttl` | `300.0` | Seconds after which a store that expires locks, such as Redis, releases a lock whose process died. Keep it longer than your slowest PSP call |

## Load the payment through Payum

The lock protects the payment only if it is loaded after the lock is taken. Payum does that when you
dispatch a command on a token: `CaptureCommand::forToken($token)` loads the payment once it holds the
lock.

When you load the payment yourself and pass it in, with `CaptureCommand::forPayment($payment)`, the lock
still stops two commands from running at once. But the second request already holds its own copy of the
payment, loaded before the first request finished. Its handler then sees the payment as it was. Dispatch
on the token wherever you have one.

Event listeners have the same limit. Payum dispatches `CommandDispatched` before the middleware takes the
lock, so a listener that calls `$event->context->subject()` loads the payment outside the lock. Read the
payment in a `ResultReturned` listener instead. See [Events](events.md) for the list of events.

## Pass an idempotency key

A lock stops two requests from running at once. It cannot stop your own code from sending the same
capture or refund again later, for example after a timeout or when a queued job runs twice. For that,
give the command an idempotency key (a value you choose that names this one operation, such as the order
number):

```php
<?php

use Payum\Core\Command\RefundCommand;

$result = $gateway->execute(RefundCommand::forPayment($payment, amount: 500, idempotencyKey: 'order-1001-refund-1'));
```

Captures, authorizations, refunds, cancellations and payouts take a key. Use the same key when you
retry an operation and a new key for each new operation. Payum does two things with it:

- A gateway whose PSP accepts idempotency keys sends the key along, so the PSP itself refuses to do the
  operation twice.
- Payum remembers the result for 24 hours. A command that comes back with the same key gets that result
  and never reaches the gateway, whether or not the PSP accepts keys.

This works with nothing to configure, but by default Payum remembers keys only for as long as the PHP
process runs. To recognise a key in a later request, store the records, as described in the next section.

## Replay a repeated key

When a command arrives with a key Payum has already seen, it returns the first result again:

```php
<?php

use Payum\Core\Command\RefundCommand;

$first = $gateway->execute(RefundCommand::forPayment($payment, amount: 500, idempotencyKey: 'order-1001-refund-1'));
$again = $gateway->execute(RefundCommand::forPayment($payment, amount: 500, idempotencyKey: 'order-1001-refund-1'));

// The PSP was asked once. Both results carry the same transaction id and amount.
$again->transactionId === $first->transactionId;
```

The replayed result has the same status, transaction id, amount and failure as the first one. Its
`$raw` is empty: Payum never stores what the PSP sent back, because it can hold card data.

Payum remembers only results that are finished:

| First result | Remembered |
| :--- | :--- |
| Succeeded, declined, or accepted for the PSP to finish later | Yes |
| Sends the customer somewhere, such as a redirect to the PSP | No. The customer's return runs the command again, and its result is remembered |
| Failed on the network or a rate limit, which `$result->failure->isRetriable()` reports | No, so a retry reaches the PSP |
| Threw an exception | No |

Sending a key again for a different operation is a bug in your code. If the amount, the payment, the
reason or the kind of command differs from the first time, Payum throws
`Payum\Core\Exception\IdempotencyKeyReusedException` and sends nothing to the PSP.

## Store the records

Store the records in a database so a key is recognised in every request and on every server. Extend
Payum's `IdempotencyRecord` model as a Doctrine entity:

```php
<?php
namespace Acme\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\IdempotencyRecord as BaseIdempotencyRecord;

#[ORM\Entity]
#[ORM\Table(name: 'payum_idempotency_record')]
class IdempotencyRecord extends BaseIdempotencyRecord
{
}
```

The entity adds nothing. Its columns come from Payum's XML mapping, which you register the way
[Storages](storages.md#create-the-entity-manager) shows. Then register the store:

```php
<?php

use Acme\Entity\IdempotencyRecord;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Idempotency\IdempotencyStoreInterface;
use Payum\Core\Idempotency\StorageIdempotencyStore;

$builder->addGlobalService(
    IdempotencyStoreInterface::class,
    new StorageIdempotencyStore(new DoctrineStorage($entityManager, IdempotencyRecord::class)),
);
```

`StorageIdempotencyStore` takes any Payum storage. Payum also ships a Doctrine MongoDB ODM mapping for the
model, so a document that extends it works the same way. Expired records stay in the table until a newer
record with the same key replaces them. Delete rows whose `expires_at` has passed from a scheduled job if
the table grows too large.

{% hint style="warning" %}
Only `LockingMiddleware` makes replay safe for two requests that arrive at the same time. Without it,
both find no record, both reach the gateway, and the PSP's own idempotency key is the only thing left to
stop a second charge. Turn on [locking](#turn-on-locking) as well.
{% endhint %}

With locking on, a second request with the same key waits for the first. Once it gets the lock, it finds
the first result and returns it.

## Advanced: nested commands and 1.x gateways

A handler can dispatch another command about the same payment with `$context->execute()`. The nested
command runs inside the lock its caller already holds, so it does not wait for itself. This only holds
on the same gateway. A command about the same payment on a different gateway, dispatched from inside a
handler, waits for the lock and then throws `ConcurrentOperationException`.

A gateway built only from 1.x actions takes no lock, even with the middleware registered. Once the gateway
has a handler for a command, that command is locked, whether you dispatch the command or send the 1.x
request. See
[Migrate a gateway from 1.x](gateways/migrating-from-v1.md) to move a gateway to handlers.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
