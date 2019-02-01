<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Payum Bundle. Configure gateway in backend

In [get it started](get-it-started.md) we showed you how to configure gateways in the Symfony config.yml file. 
Though it covers most of the cases sometimes you may want to configure gateways in the backend. 
For example you will be able to change a gateway credentials, add or delete a gateway.

PayumBundle comes with [Sonata Admin](http://sonata-project.org/bundles/admin/2-3/doc/index.html) bundle support out of the box, but you can totally do it manually.

## Configure

First we have to create an entity where we store information about a gateway. 
The model must implement `Payum\Core\Model\GatewayConfigInterface`.

_**Note**: In this chapter we show how to use Doctrine ORM entities. There are other supported [storages](storages.md)._

```php
<?php
namespace Acme\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\GatewayConfig as BaseGatewayConfig;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class GatewayConfig extends BaseGatewayConfig
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var integer $id
     */
    protected $id;
}
```

### With Sonata Admin

Next, you have to add mapping of the basic entity you've just extended, and configure payum's extension:

```yml
#app/config/config.yml

payum:
    dynamic_gateways:
        sonata_admin: true
        config_storage: 
            Acme\PaymentBundle\Entity\GatewayConfig: { doctrine: orm }
```

#### Backend

Once you have configured everything doctrine, payum and sonata admin go to `/admin/dashboard`. 
There you have to see a `Gateways` section. Try to add a gateway there.

### The manual way

```yml
#app/config/config.yml

payum:
    dynamic_gateways:
        config_storage: 
            Acme\PaymentBundle\Entity\GatewayConfig: { doctrine: orm }
```

#### Backend

The following code is a basic example for configuring a [Paypal Express Checkout](https://github.com/Payum/Payum/blob/master/docs/paypal/express-checkout/get-it-started.md) gateway.

We first need to create a FormType with three fields:
  1. `factoryName`, the name of a factory, in our case it will always be `paypal_express_checkout`
  2. `gatewayName`, the name you want to give to your gateway
  3. `config`, the gateway configuration

```php
<?php
// src/Acme/PaymentBundle/Form/Type/GatewayConfigType.php

namespace Acme\PaymentBundle\Form\Type;

use Acme\PaymentBundle\Entity\GatewayConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaypalGatewayConfigType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {        
        $builder
            ->add('factoryName', TextType::class, [
                'disabled' => true,
                'data' => 'paypal_express_checkout',
            ])
            ->add('gatewayName', TextType::class)
            ->add('config', ConfigPaypalGatewayConfigType::class, [
                'label' => false,
                'auto_initialize' => false,
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GatewayConfig::class,
        ]);
    }
}
```

Then, we should implement a new FormType that will configure your PayPal gateway's config.

By reading [the doc](https://github.com/Payum/Payum/blob/master/docs/paypal/express-checkout/get-it-started.md), we should create four fields:
  1. `sandbox`
  2. `username`
  3. `password`
  4. `signature`
  

```php
<?php
// src/Acme/PaymentBundle/Form/Type/PaypalGatewayConfigType.php

namespace Acme\PaymentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class ConfigPaypalGatewayConfigType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { 
        $builder
            ->add('sandbox', CheckboxType::class)
            ->add('username', TextType::class)
            ->add('password', TextType::class)
            ->add('signature', TextType::class)
        ;
    }
}
```

For a more advanced example, you can check how Sylius implemented [Paypal and Stripe gateways form types](https://github.com/Sylius/Sylius/tree/master/src/Sylius/Bundle/PayumBundle/Form/Type).

## Use gateway

Let's say you created a gateway with name `paypal`. Here we will show you how to use it.

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php

namespace Acme\PaymentBundle\Controller;

class PaymentController extends Controller 
{
    public function prepareAction() 
    {
        // If you have linked a gateway config to your user, you can simply use:
        $gatewayName = $this->getUser()->getGatewayConfig()->getGatewayName();
        
        $storage = $this->get('payum')->getStorage('Acme\PaymentBundle\Entity\Payment');
        
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');
        
        $storage->update($payment);
        
        $captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
            $gatewayName, 
            $payment, 
            'done' // the route to redirect after capture
        );
        
        return $this->redirect($captureToken->getTargetUrl());    
    }
}
```

_**Note**: If you configured a gateway in config.yml and in the backend with same name. Backend one will be used._

* [Back to index](../index.md).


 
 

