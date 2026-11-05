# Tokens and payment URLs

A payment crosses several HTTP requests: your checkout page, the capture script, the provider's site and
back, then the page that shows the outcome. Each of those requests has to know which payment it is
about and which gateway to use, without trusting anything the browser could change. Payum solves this
with tokens. A token is a random hash, stored on your side, that names a gateway and a payment. Its URL
carries only the hash, as the `payum_token` query parameter.

## Create a token

`Payum::prepare()` saves a payment and creates the capture token that starts it:

```php
$token = $payum->prepare('acme', $payment);

header('Location: '.$token->getTargetUrl());
```

For any other operation, create the token through the token issuer and name its purpose:

```php
<?php

use Payum\Core\Security\TokenPurpose;

$payum->getStorage($payment::class)->update($payment);

$token = $payum->getTokenIssuer()->create(TokenPurpose::Authorize, 'acme', $payment, 'done.php');
```

`create()` takes the purpose, the gateway name, the payment, an after path and an array of after
parameters. Save the payment first. The token stores the payment's identity, which the storage assigns.
`prepare()` saves it for you.

A token has two URLs:

- The target URL runs the operation. `$token->getTargetUrl()` is where you send the customer.
- The after URL is where the customer lands once the operation is done. `$token->getAfterUrl()` is a
  second token's URL, so the page there can verify it and load the same payment.

Each purpose has its own target path and its own rule for the after URL:

| Purpose | Target path | After URL |
| :--- | :--- | :--- |
| `TokenPurpose::Capture` | `capture` | The after path you pass, or the `done` path. |
| `TokenPurpose::Authorize` | `authorize` | The after path you pass. Required. |
| `TokenPurpose::Payout` | `payout` | The after path you pass. Required. |
| `TokenPurpose::Refund` | `refund` | The after path you pass, or none. |
| `TokenPurpose::Notify` | `notify` | None. The provider calls this URL, not a browser. |

{% hint style="warning" %}
Always pass an after path for `Authorize` and `Payout`. Without one, `create()` fails with a `TypeError`.
{% endhint %}

The after parameters are added to the after URL's query string, which is useful for passing your own
order number along:

```php
$token = $payum->prepare('acme', $payment, 'thanks.php', ['order' => 1001]);
```

You rarely create a notify token yourself. A gateway whose provider takes a notification URL per payment
creates one while it runs the capture. See [Payment notifications](instant-payment-notification.md).

## Choose where token URLs point

By default, every path is a PHP script next to the one that creates the token:

| Path | Default |
| :--- | :--- |
| `capture` | `capture.php` |
| `authorize` | `authorize.php` |
| `refund` | `refund.php` |
| `payout` | `payout.php` |
| `notify` | `notify.php` |
| `done` | `done.php` |

Change any of them on the builder. Paths you do not pass keep their default:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->setGenericTokenFactoryPaths([
        'capture' => 'payment/capture',
        'notify' => 'payment/notify',
        'done' => 'https://shop.example.com/thanks',
    ])
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

A path that starts with `http` is used as it is. Any other path is resolved against the URL of the
current request. When that URL ends in `.php`, the path replaces the script name. Otherwise it is
appended to the URL. An after path passed to `create()` or `prepare()` follows the same rules.

### Create tokens outside a web request

A cron job or a queue worker has no current URL to resolve paths against. Give the token factory a base
URL instead:

```php
<?php

use Acme\Payum\AcmeConfig;
use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->setTokenFactory(static fn ($tokenStorage, $storageRegistry) => new TokenFactory(
        $tokenStorage,
        $storageRegistry,
        'https://shop.example.com',
    ))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

With this base URL, the default capture path becomes `https://shop.example.com/capture.php`. The
framework integrations generate URLs from routes instead, so you do not need this there.

## Verify a token

A script that a token URL leads to starts by verifying the token:

```php
<?php

use GuzzleHttp\Psr7\ServerRequest;

$token = $payum->getHttpRequestVerifier()->verify(ServerRequest::fromGlobals());

$token->getGatewayName();   // 'acme'
$token->getDetails();       // the payment's identity: its class and its id
```

`verify()` takes a PSR-7 server request. It looks for the hash in a request attribute named
`payum_token`, then in the query string, then in the parsed body, and loads the token from token
storage. It throws `Payum\Core\Exception\InvalidArgumentException` when:

- the request has no `payum_token`,
- no token with that hash exists, because it was never issued or has been deleted,
- the request's path is not the path of the token's target URL.

The last check stops a token issued for `done.php` from being replayed against `capture.php`. Catch the
exception and answer with a 404 or a 400, as you would for any other bad link.

Any PSR-7 implementation works. `ServerRequest` here comes from `guzzlehttp/psr7`. Do not pass
`$_REQUEST`: that form is deprecated and is removed in 3.0.

To load the payment a token points at, look its identity up in storage:

```php
$identity = $token->getDetails();

$payment = $payum->getStorage($identity->getClass())->find($identity);
```

## Invalidate a token

A token stays valid until you delete it. Delete it once the operation is finished, so the URL cannot run
it again:

```php
$payum->getHttpRequestVerifier()->invalidate($token);
```

Do it at the end of the operation, not on the first request. A capture on a redirect gateway reaches
`capture.php` once to send the customer away and again when the customer comes back, and both requests
use the same token. The `respond()` function in [Payment operations](payment-operations.md) deletes the
token when the result has no next action, and the done script deletes its own token after reading the
status.

Two cases are different. `Payum::capture()` leaves the capture token in storage, so delete it yourself if
you use it. Never delete a notify token, because the provider may call the same URL many times for one payment.

## Store tokens

Tokens live in the token storage. `addDefaultStorages()` keeps them as files in the system temporary
directory, which is fine for trying Payum out and loses tokens whenever that directory is cleared. Use a
database for real payments:

```php
<?php

use App\Entity\PaymentToken;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\PayumBuilder;

$payum = (new PayumBuilder())
    ->setTokenStorage(new DoctrineStorage($entityManager, PaymentToken::class))
    // ...
    ->getPayum();
```

`PaymentToken` is your own entity that extends `Payum\Core\Model\Token`. `$payum->getTokenStorage()`
returns the storage in use. Tokens pile up over time, because a payment that is abandoned halfway never
reaches the point where its token is deleted, so clean out old rows now and then.

A token's details are stored as serialized PHP by default. With Doctrine ORM you can store them as JSON
instead, which is readable in any database tool and means reading a token unserializes nothing. It takes
a migration and a listener. [Storages](storages.md) has the steps, under "Storing token details as JSON".

## Advanced: replace the token issuer

`$payum->getTokenIssuer()` returns the `Payum\Core\Security\TokenIssuerInterface` service, which every
gateway uses too. To mint tokens your own way, for example from your framework's router, implement the
interface and register it as a global service:

```php
<?php

use Payum\Core\PayumBuilder;
use Payum\Core\Security\TokenIssuerInterface;

$payum = (new PayumBuilder())
    ->addGlobalService(TokenIssuerInterface::class, new RouterTokenIssuer($router, $tokenStorage))
    // ...
    ->getPayum();
```

`RouterTokenIssuer` stands for your class. Its `create()` receives the purpose, the gateway name, the
payment, the after path and the after parameters, and returns a stored token whose target URL leads to
the script or route for that purpose. [Replace and add services](di/customization.md) covers global
services in general.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
