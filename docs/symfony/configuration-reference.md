# Configuration reference

This page lists every key PayumBundle reads under `payum` in `config/packages/payum.yaml`.

## Overview

```yaml
# config/packages/payum.yaml
payum:
    security:
        token_storage:
            App\Entity\PaymentToken: { doctrine: orm }

    locking: true

    idempotency:
        cache_pool: cache.app

    storages:
        App\Entity\Payment:
            doctrine: orm
            extension:
                all: true
                gateways: []
                factories: []

    gateways:
        offline:
            config: Payum\Offline\OfflineConfig
            arguments:
                paid: true

        paypal:
            factory: paypal_express_checkout
            username: '%env(PAYPAL_USERNAME)%'
            password: '%env(PAYPAL_PASSWORD)%'
            signature: '%env(PAYPAL_SIGNATURE)%'
            sandbox: true
```

`security.token_storage` is required and takes exactly one token class and the storage for it. `storages`
takes one entry per model class. The optional `extension` block chooses which gateways load and save that
model for you. By default all of them do. List gateways or factories to limit it to those.

## Storages

Store a model with Doctrine ORM or MongoDB ODM:

```yaml
payum:
    storages:
        App\Entity\Payment: { doctrine: orm } # or mongodb
```

Store a model in files, for tests only:

```yaml
payum:
    storages:
        App\Model\Payment:
            filesystem:
                storage_dir: '%kernel.project_dir%/var/payments'
                id_property: number
```

[Storages](storages.md) also covers custom storages.

## Locking

Lets one request at a time work on a payment, with Symfony Lock. Off by default:

```yaml
payum:
    locking:
        enabled: true
        lock_factory: lock.factory
        timeout: 10.0
        ttl: 300.0
```

| Key | Default | What it does |
| :--- | :--- | :--- |
| `enabled` | `false` | Turns locking on. `locking: true` is short for it. Needs `symfony/lock`. |
| `lock_factory` | `lock.factory` | The service that creates the locks. |
| `timeout` | `10.0` | Seconds a request waits for another one before Payum throws `ConcurrentOperationException`. |
| `ttl` | `300.0` | Seconds after which a store that expires locks releases a lock whose process died. |

## Idempotency

Where Payum keeps idempotency records, so a repeated key is recognised in a later request. Set exactly one
of `cache_pool`, `dbal_connection` or `service`:

```yaml
payum:
    idempotency:
        cache_pool: cache.app
```

| Key | Default | What it does |
| :--- | :--- | :--- |
| `cache_pool` | | Keeps the records in the cache pool with this service id. |
| `dbal_connection` | | Keeps the records in a table over the Doctrine DBAL connection with this service id. |
| `table` | `payum_idempotency_record` | The table `dbal_connection` writes to. |
| `service` | | Your own service implementing `Payum\Core\Idempotency\IdempotencyStoreInterface`. |

[Stop double captures](concurrency-and-idempotency.md) explains both sections.

## Gateways registered by their config class

| Key | What it does |
| :--- | :--- |
| `config` | The gateway's config class. It must implement `Payum\Core\Config\GatewayConfig`. |
| `arguments` | The arguments of the config class's constructor, by parameter name. `%env()%` works. |

An entry with `config` takes no other keys. See [Register gateways](gateways.md).

## Gateways registered with `factory`

Gateways that have no config class yet take a `factory` name and that factory's options. The bundle
passes the options to the factory as they are, so each gateway documents its own. The gateways in this
repository take these:

### Paypal express checkout gateway

```yaml
payum:
    gateways:
        aName:
            factory: paypal_express_checkout
            username:  'required'
            password:  'required'
            signature: 'required'
            sandbox: true
```

### Paypal pro checkout gateway

```yaml
payum:
    gateways:
        aName:
            factory: paypal_pro_checkout
            username:  'required'
            password:  'required'
            partner: 'required'
            vendor: 'required'
            tender: C
            trxtype: S
            sandbox: true
```

### Stripe.Js

```yaml
payum:
    gateways:
        aName:
            factory: stripe_js
            publishable_key: 'required'
            secret_key: 'required'
```

### Stripe Checkout

```yaml
payum:
    gateways:
        aName:
            factory: stripe_checkout
            publishable_key: 'required'
            secret_key: 'required'
```

### Authorize.Net AIM gateway

```yaml
payum:
    gateways:
        aName:
            factory: authorize_net_aim
            login_id: 'required'
            transaction_key: 'required'
            sandbox: true
```

### Be2Bill gateway

```yml
payum:
    gateways:
        aName:
            factory: be2bill
            identifier: 'required'
            password: 'required'
            sandbox: true
```

### Be2Bill onsite gateway

```yml
payum:
    gateways:
        aName:
            factory: be2bill_onsite
            identifier: 'required'
            password: 'required'
            sandbox: true
```

### Payex gateway

```yml
payum:
    gateways:
        aName:
            factory: payex
            encryption_key: 'required'
            account_number: 'required'
            sandbox: true
```

### Klarna checkout gateway

```yml
payum:
    gateways:
        aName:
            factory: klarna_checkout
            secret:  'required'
            merchant_id: 'required'
            sandbox: true
```

### Klarna invoice gateway

```yml
payum:
    gateways:
        aName:
            factory: klarna_invoice
            secret: 'required'
            eid: 'required'
            country: SE
            language: SV
            currency: SEK
            sandbox: true
```

### Omnipay gateway

```yml
payum:
    gateways:
        aName:
            factory: omnipay
            type: 'required'
            options:
                foo: fooOpt
                bar: barOpt
```

### Custom gateway

```yaml
payum:
    gateways:
        aName:
            factory: custom
            # if service not set an empty gateway will be created.
            service: ~
```

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
