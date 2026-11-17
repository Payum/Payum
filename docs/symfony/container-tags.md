# Container tags

PayumBundle reads a few Symfony container tags. Tag a service with one of them to hand it to Payum as a
gateway, as a service gateways can use, or as an action, API or extension of a 1.x gateway.

## `payum.gateway_config`

Registers a gateway from a service that implements `Payum\Core\Config\GatewayConfig`:

```yaml
# config/services.yaml
services:
    app.payum.offline:
        class: Payum\Offline\OfflineConfig
        arguments:
            $paid: '%env(bool:OFFLINE_PAID)%'
        tags:
            - { name: payum.gateway_config, gateway: offline }
```

| Attribute | Required | What it does |
| :--- | :--- | :--- |
| `gateway` | yes | The name the gateway is registered under. It must not be used by another gateway. |

[Register gateways](gateways.md) explains when to use the tag instead of `payum.gateways` in
`config/packages/payum.yaml`.

## `payum.global_service`

Shares a service with every gateway, so a handler or action can take it in its constructor:

```yaml
# config/services.yaml
services:
    App\Payment\ExchangeRates:
        tags:
            - { name: payum.global_service, id: App\Payment\ExchangeRatesInterface }
```

| Attribute | Required | What it does |
| :--- | :--- | :--- |
| `id` | no | The id Payum knows the service under. Defaults to the service id. |

A shared service also replaces Payum's default for the same id. See
[Use your services in Payum](services.md).

## `payum.gateway`

Registers a gateway object you build yourself. The service must implement `Payum\Core\GatewayInterface`:

```yaml
# config/services.yaml
services:
    app.payum.foo_gateway:
        class: Payum\Core\Gateway
        public: true
        tags:
            - { name: payum.gateway, gateway: foo }
```

| Attribute | Required | What it does |
| :--- | :--- | :--- |
| `gateway` | yes | The name the gateway is registered under. |

## `payum.action`, `payum.api` and `payum.extension`

These tags add an action, an API object or an extension to gateways built from a factory, the 1.x way.
A gateway registered by its config class runs handlers and middleware instead. It still gets the
services tagged with `all: true`, but never tag a service with the name of such a gateway under
`gateway`. Payum then fails to build.

```yaml
# config/services.yaml
services:
    App\Payment\FooAction:
        tags:
            - { name: payum.action, gateway: paypal, prepend: false }
```

Here `paypal` is a gateway registered with `factory`. An action must implement
`Payum\Core\Action\ActionInterface` and an extension
`Payum\Core\Extension\ExtensionInterface`. An API can be any object. All three tags take the same
attributes:

| Attribute | What it does |
| :--- | :--- |
| `gateway` | Adds the service to the gateway with this name. |
| `factory` | Adds the service to every gateway built from this factory. |
| `all` | When `true`, adds the service to every gateway. |
| `prepend` | When `true`, puts the service before the ones the gateway already has, so it runs first. |
| `alias` | With Payum 1, the name of the entry, so a tagged service can replace a default one. Payum 2 ignores it. |

## `payum.gateway_factory`

Registers a 1.x gateway factory, so `factory` in `payum.gateways` can name it. The service must implement
`Payum\Core\GatewayFactoryInterface`:

```yaml
# config/services.yaml
services:
    app.payum.foo_gateway_factory:
        class: App\Payment\FooGatewayFactory
        tags:
            - { name: payum.gateway_factory, factory: foo }
```

| Attribute | Required | What it does |
| :--- | :--- | :--- |
| `factory` | yes | The factory name to use under `factory` in `payum.gateways`. |

## Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
