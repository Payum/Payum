# Custom action usage

Payment comes with built in actions but sometime you have to add your own. First you have to define a service:

```yaml
# src/Acme/PaymentBundle/Resources/config/services.yml

services:
    acme.payum.action.foo:
        class: Acme\PaymentBundle\Payum\Action\FooAction
```

There are several ways to add it to a payment:

* Set it explicitly in config.yml. 

    ```yaml
    # app/config/config.yml

    payum:
        contexts:
            a_context:
                a_factory:
                    actions:
                        - acme.payum.action.foo
    ```

* Tag it

    
    More powerful method is to add a tag `payum.action` to action server. Payum will do the reset.
    You can define a `factory` attribute inside that tag. 
    In this case the action will be added to all payments created by requested factory.
 
    ```yaml
    # app/config/config.yml

    payum:
        contexts:
            a_context:
                a_factory: ~
    ```

    ```yaml
    # src/Acme/PaymentBundle/Resources/config/services.yml

    services:
        acme.payum.action.foo:
            class: Acme\PaymentBundle\Payum\Action\FooAction
            tags:
                - {payum.action, { factory: a_factory }}

    ```

    Or you can set concrete `context` name. 
    In this case the action will be added only to the payment with requested context name.

    ```yaml
    # app/config/config.yml

    payum:
        contexts:
            a_context:
                a_factory: ~
    ```

    ```yaml
    # src/Acme/PaymentBundle/Resources/config/services.yml

    services:
        acme.payum.action.foo:
            class: Acme\PaymentBundle\Payum\Action\FooAction
            tags:
                - {payum.action, {context: a_context}}
    ```

    If `prepend` set to true the action is added before the rest. 
    If you want to add the action to all configured payments set `all` to true.

    ```yaml
    # src/Acme/PaymentBundle/Resources/config/services.yml

    services:
        acme.payum.action.foo:
            class: Acme\PaymentBundle\Payum\Action\FooAction
            tags:
                - {payum.action, { prepend: true, all: true }}
    ```

Back to [index](index.md).
