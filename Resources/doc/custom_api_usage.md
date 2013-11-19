# Custom api usage

Sometime you asked to store payment gateway credentials to database.
That's required to allow an admin change them quickly in the site backend.
By default payum use container parameters to store such information and
here in this chapter we would show how to use custom api.

## Api factory

First, we have to create an api factory.
The factory would create the desired api using database or what ever else you want.

```php
<?php
// src/Acme/PaymentBundle/Payum/Api/Factory.php;
namespace Acme\PaymentBundle\Payum\Api;

use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Factory
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Api
     */
    public function createPaypalExpressCheckoutApi()
    {
        return new Api($this->container->get('payum.buzz.client'), array(
            'username' => $this->container->getParameter('paypal.express_checkout.username'),
            'password' => $this->container->getParameter('paypal.express_checkout.password'),
            'signature' => $this->container->getParameter('paypal.express_checkout.signature'),
            'sandbox' => true
        ));
    }
}
```

As you could see we use container to build paypal api.
Feel free to change it to suit your needs.
Now we have to create an api service which is created by the factory one:

```yaml
# src/Acme/PaymentBundle/Resources/config/services.yml

services:

    # ...

    acme.payment.payum.api.factory:
        class: Acme\PaymentBundle\Payum\Api\Factory
        arguments:
            - @service_container

    acme.payment.payum.paypal_express_checkout_api:
        class: Payum\Paypal\ExpressCheckout\Nvp\Api
        factory_service: acme.payment.payum.api.factory
        factory_method: createPaypalExpressCheckoutApi
```

When we are done we can tell payum to use this service instead of default one:

```yaml
# app/config/config.yml

payum:
    contexts:
        your_context_name_here:
            paypal_express_checkout_nvp:
                api:
                    options:
                        username:  NOT USED
                        password:  NOT USED
                        signature: NOT USED
                        sandbox: true
                apis:
                    - acme.payment.payum.paypal_express_checkout_api

```

That's it!

Back to [index](index.md).
