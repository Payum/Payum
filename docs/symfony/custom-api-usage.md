# Payum Bundle. Custom api usage

Sometime you asked to store payment gateway credentials to database. 
If this is your case read [Configure payment in backend](configure-payment-in-backend.md) chapter.
Here we would describe how you can add an api defined as service.

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
        return new Api(array(
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
    gateways:
        your_gateway_name_here:
            factory: paypal_express_checkout
            username:  NOT USED
            password:  NOT USED
            signature: NOT USED
            sandbox: true
            payum.api: @acme.payment.payum.paypal_express_checkout_api

```

That's it!

* [Back to index](../index.md).
