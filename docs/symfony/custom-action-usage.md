# Payum Bundle. Custom action usage

Gateway comes with built in actions but sometime you have to add your own. First you have to define a service:

```yaml
# src/Acme/PaymentBundle/Resources/config/services.yml

services:
    acme.payum.action.foo:
        class: Acme\PaymentBundle\Payum\Action\FooAction
```

There are several ways to add it to a gateway:

* Set it explicitly in config.yml. 

    ```yaml
    # app/config/config.yml

    payum:
        gateways:
            a_gateway:
                factory: a_factory:
                payum.action.foo: @payumActionServiceId
    ```

* Tag it

    
    More powerful method is to add a tag `payum.action` to action server. Payum will do the reset.
    You can define a `factory` attribute inside that tag. 
    In this case the action will be added to all gateways created by requested factory.
 
    ```yaml
    # app/config/config.yml

    payum:
        gateways:
            a_gateway:
                factory: a_factory
    ```

    ```yaml
    # src/Acme/PaymentBundle/Resources/config/services.yml

    services:
        acme.payum.action.foo:
            class: Acme\PaymentBundle\Payum\Action\FooAction
            tags:
                - { name: payum.action, factory: a_factory }

    ```

    If `prepend` set to true the action is added before the rest. 
    If you want to add the action to all configured gateways set `all` to true.

    ```yaml
    # src/Acme/PaymentBundle/Resources/config/services.yml

    services:
        acme.payum.action.foo:
            class: Acme\PaymentBundle\Payum\Action\FooAction
            tags:
                - {name: payum.action, prepend: true, all: true }
    ```

* [Back to index](../index.md).
