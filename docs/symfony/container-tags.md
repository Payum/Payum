<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Payum Bundle. Container tags

The bundle supports\reuse several Symfony container tags. You may find them useful too.
 
## Gateway tag

The tag `payum.gateway` could be used if you want to register your service as a gateway gateway. 
The service must implement `Payum\Core\GatewayInterface`.

```yaml
# app/config/config.yml

services:
    acme.foo_gateway:
        class: Payum\Core\Gateway
        public: true
        tags:
            - { name: payum.gateway, gateway: foo }
```

Attributes:

* factory - define if you want later add actions or extensions to all gateways created by this factory.

* gateway - define if you want later add actions or extensions to this gateway.

## Action tag

The tag `payum.action` could be used if you want to register your service as an action. 
The service must implement `Payum\Core\Action\ActionInterface`.

```yaml
# app/config/config.yml

services:
    acme.foo_action:
        class: Payum\Core\Action\ActionInterface
        public: true
        tags:
            - { name: payum.action, factory: foo, gateway: bar, all: true, alias: foo, prepend: false }
```

Attributes:

* factory - define if you want to add the action to gateways created by the factory with given name.

* gateway - define if you want to add the action to a gateway with given name

* all - define if you want to add the action to all gateways

* prepend - define if want your action to be put at the begging.

* alias - you can use alias if you' like to overwrite the default action or add a good looking name

## Api tag

The tag `payum.api` could be used if you want to register your service as an api.
The service could be any object.

```yaml
# app/config/config.yml

services:
    acme.foo_api:
        class: Acme\Payum\FooApi
        public: true
        tags:
            - { name: payum.api, factory: foo, gateway: bar, all: true, alias: foo, prepend: false }
```

Attributes:

* factory - define if you want to add the extension to gateways created by the factory with given name.

* gateway - define if you want to add the extension to a gateway with given name

* all - define if you want to add the extension to all gateways

* prepend - define if want your extension to be put at the begging.

* alias - you can use alias if you' like to overwrite the default action or add a good looking name

## Extension tag

The tag `payum.extension` could be used if you want to register your service as an extension. 
The service must implement `Payum\Core\Extension\ExtensionInterface`.

```yaml
# app/config/config.yml

services:
    acme.foo_extension:
        class: Payum\Core\Extension\ExtensionInterface
        public: true
        tags:
            - { name: payum.extension, factory: foo, gateway: bar, all: true, alias: foo, prepend: false }
```

Attributes:

* factory - define if you want to add the extension to gateways created by the factory with given name.

* gateway - define if you want to add the extension to a gateway with given name

* all - define if you want to add the extension to all gateways

* prepend - define if want your extension to be put at the begging.

* alias - you can use alias if you' like to overwrite the default action or add a good looking name

## Gateway factory tag

The tag `payum.gateway_factory` could be used if you want to register your service as a gateway factory. 
The service must implement `Payum\Core\GatewayFactoryInterface`.

```yaml
# app/config/config.yml

services:
    acme.foo_gateway_factory:
        class: Payum\Core\GatewayFactory
        public: true
        tags:
            - { name: payum.gateway_factory, factory: foo }
```

Attributes:

* name - The name of the factory

* human_name - The name shown to humans, in the backend for example.

* [Back to index](../index.md).

