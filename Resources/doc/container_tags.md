# Container tags

The bundle supports\reuse several Symfony container tags. You may find them useful too.
 
## Payment tag

The tag `payum.payment` could be used if you want to register your service as a payment gateway. 
The service must implement `Payum\Core\PaymentInterface`.

```yaml
# app/config/config.yml

services:
    acme.foo_payment:
        class: Payum\Core\Payment
        tags:
            - { name: payum.payment, factory: custom, payment: foo }
```

Attributes:

* factory - define if you want later add actions or extensions to all payments created by this factory.

* payment - define if you want later add actions or extensions to this payment.

## Action tag

The tag `payum.action` could be used if you want to register your service as an action. 
The service must implement `Payum\Core\Action\ActionInterface`.

```yaml
# app/config/config.yml

services:
    acme.foo_action:
        class: Payum\Core\Action\ActionInterface
        tags:
            - { name: payum.action, factory: foo, payment: bar, all: true, prepend: false }
```

Attributes:

* factory - define if you want to add the action to payments created by the factory with given name.

* payment - define if you want to add the action to a payment with given name

* all - define if you want to add the action to all payments

* prepend - define if want your action to be put at the begging.

## Extension tag

The tag `payum.extension` could be used if you want to register your service as an extension. 
The service must implement `Payum\Core\Extension\ExtensionInterface`.

```yaml
# app/config/config.yml

services:
    acme.foo_extension:
        class: Payum\Core\Extension\ExtensionInterface
        tags:
            - { name: payum.extension, factory: foo, payment: bar, all: true, prepend: false }
```

Attributes:

* factory - define if you want to add the extension to payments created by the factory with given name.

* payment - define if you want to add the extension to a payment with given name

* all - define if you want to add the extension to all payments

* prepend - define if want your extension to be put at the begging.

## Payment factory tag

The tag `payum.payment_factory` could be used if you want to register your service as a payment factory. 
The service must implement `Payum\Core\PaymentFactoryInterface`.

```yaml
# app/config/config.yml

services:
    acme.foo_payment_factory:
        class: Payum\Core\PaymentFactory
        tags:
            - { name: payum.payment, name: foo, human_name: Foo }
```

Attributes:

* name - The name of the factory

* human_name - The name shown to humans, in the backend for example.

## Next Step

* [Get it started](get_it_started.md).
* [Custom purchase examples](custom_purchase_examples.md).
* [Back to index](index.md).

