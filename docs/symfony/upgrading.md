# Upgrade to Payum 2

PayumBundle works with Payum 1 and Payum 2, and it detects which one is installed. Your bundle
configuration keeps working after the upgrade, and so do your gateways, the bundle's routes and your
controllers. This page takes you through the update, the few things Payum 2 changes in your database
and configuration, and then moving each gateway and controller to the Payum 2 way at your own pace.

## Check your requirements

Payum 2 needs PHP 8.1 or later and Symfony 6.4, 7 or 8. Update Symfony first if your application runs
an older version. [Upgrade from 1.x](../upgrading.md#update-your-requirements) lists the other libraries
Payum 2 needs, such as Doctrine and Twig.

## Update the packages

Update the bundle, Payum and your gateway packages together:

```bash
composer require payum/payum-bundle payum/core:^2.0 payum/offline:^2.0 --with-all-dependencies
```

Name every gateway package you use, each with `^2.0`. If you installed `payum/payum`, which contains Payum
and every gateway in this repository, require `payum/payum:^2.0` instead, and remove any gateway package
of this repository that you also listed on its own. Composer refuses to install both.

## Fix what breaks

The bundle needs no changes. These come from Payum itself:

- **The payment table gains a `status` column.** Payum now records the status of each payment on it.
  Generate a migration with `bin/console doctrine:migrations:diff`, or run
  `bin/console doctrine:schema:update --force`. If your `Payment` entity already has a `status`
  property, read
  [Payments and payouts record their status](../upgrading.md#payments-and-payouts-record-their-status)
  first.
- **The Propel storages are gone.** Replace `propel1` or `propel2` under `payum.storages` with
  `doctrine`, `filesystem` or `custom`. See [Storages](storages.md).
- **A custom core gateway factory must support Payum 2.** If you replaced the
  `payum.core_gateway_factory_builder` service, the factory it builds must implement
  `Payum\Core\DI\ContainerConfiguration`. Extend `Payum\Bundle\PayumBundle\PayumCoreGatewayFactory` to
  keep what the bundle adds.
- **The `alias` attribute of `payum.action`, `payum.api` and `payum.extension` is ignored.** A tagged
  service is added to the gateway next to the defaults and no longer replaces one by name.

[Upgrade from 1.x](../upgrading.md#fix-what-breaks) lists the remaining changes in Payum's own classes.
They only affect code that extends or calls those classes directly.

## Check that it runs

Clear the cache and run your test suite:

```bash
bin/console cache:clear
bin/phpunit
```

Then take a test payment through every gateway you use. Payum 2 triggers a deprecation notice for each
gateway registered with `factory` and for each kind of 1.x request your code executes. They show up in the
profiler's log panel and do not stop anything from working. The rest of this page removes them.

## Move a gateway to its config class

A gateway package written for Payum 2 ships a config class. Replace `factory` and its options with
`config` and `arguments`. Before:

```yaml
# config/packages/payum.yaml
payum:
    gateways:
        offline:
            factory: offline
```

After:

```yaml
# config/packages/payum.yaml
payum:
    gateways:
        offline:
            config: Payum\Offline\OfflineConfig
```

Keep the gateway's name. Tokens and stored payments carry it, so payments started before the switch
finish on the same gateway. Read the gateway's own `UPGRADE.md` for payments that are in progress while
you switch, and for the options that became constructor arguments. Gateways whose package has no config
class yet keep `factory` until it ships one. [Register gateways](gateways.md) covers the options,
environment variables and tagged config services.

A gateway registered by its config class runs handlers and middleware, not actions and extensions:

- A service tagged `payum.action`, `payum.api` or `payum.extension` with this gateway's name under
  `gateway` stops the build. Remove the tag. To replace one of the gateway's handlers, pass yours to the
  config class, as `OfflineConfig` allows with its `handlers` argument. To change what happens around
  every command, write a [middleware](../middleware.md).
- An extension that reacted to what a gateway did becomes an event listener. See
  [Use your services in Payum](services.md#listen-to-payums-events).

## Replace the deprecated code in your controllers

Each change below stands on its own, so you can make them in any order.

### Create the capture token with prepare()

`createCaptureToken()` is deprecated. Before:

```php
$storage = $payum->getStorage(Payment::class);
$storage->update($payment);

$captureToken = $payum->getTokenFactory()->createCaptureToken('offline', $payment, 'app_payment_done');
```

After:

```php
$captureToken = $payum->prepare('offline', $payment, 'app_payment_done');
```

`prepare()` saves the payment and creates the capture token in one call. For the other token types, use
`$payum->getTokenIssuer()->create()` with a `Payum\Core\Security\TokenPurpose`. See
[Tokens and payment URLs](../tokens.md).

### Read the status in the done action

`GetHumanStatus` is deprecated. Before:

```php
$token = $payum->getHttpRequestVerifier()->verify($request);
$gateway = $payum->getGateway($token->getGatewayName());

$gateway->execute($status = new GetHumanStatus($token));
$payment = $status->getFirstModel();

if ($status->isCaptured()) {
    // ...
}
```

After:

```php
use Payum\Core\Result\PaymentStatus;

$payment = $payum->done($request);

if (PaymentStatus::Captured === $payment->getStatus()) {
    // ...
}
```

`done()` verifies the token, invalidates it and returns the payment with its status. Your `Payment` entity
has `getStatus()` because it extends `Payum\Core\Model\Payment`.

{% hint style="warning" %}
Make this change only for gateways registered by their config class. Payum records the status on the
payment when it runs a command, and a gateway registered with `factory` runs 1.x requests instead, so
`getStatus()` keeps returning `PaymentStatus::New` for its payments. Keep `GetHumanStatus` for those.
{% endhint %}

### Run operations with commands

Code that executes `Capture`, `Refund` or another request on a gateway now executes a command and reads
the result. Before:

```php
$payum->getGateway('offline')->execute(new Refund($payment));
```

After:

```php
use Payum\Core\Command\RefundCommand;

$result = $payum->getGateway('offline')->execute(RefundCommand::forPayment($payment));
```

[Payment operations](../payment-operations.md) shows every command and how to act on its result.
[Upgrade from 1.x](../upgrading.md#replace-the-deprecated-code) covers the rest of Payum's deprecated
API.

## Gateways stored in the database

Gateways you manage in the backend, with `dynamic_gateways`, are always built from their factory name.
They keep working, but cannot use a config class yet.
[Configure payment in backend](configure-payment-in-backend.md) explains the setup.

## Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
