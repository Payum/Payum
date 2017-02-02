# Payum Bundle. Configure gateway in backend

In [get it started](get-it-started.md) we showed you how to configure gateways in the Symfony config.yml file. 
Though it covers most of the cases sometimes you may want to configure gateways in the backend. 
For example you will be able to change a gateway credentials, add or delete a gateway.
As a backend we use [Sonata Admin](http://sonata-project.org/bundles/admin/2-3/doc/index.html) bundle. 
Follow its doc to configure it properly 
   
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

next, you have to add mapping of the basic entity you've just extended, and configure payum's extension:

```yml
#app/config/config.yml

payum:
    dynamic_gateways:
        sonata_admin: true
        config_storage: 
            Acme\PaymentBundle\Entity\GatewayConfig: { doctrine: orm }
```

## Backend

Once you have configured everything doctrine, payum and sonata admin go to `/admin/dashboard`. 
There you have to see a `Gateways` section. Try to add a gateway there.

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
        $gatewayName = 'paypal';
        
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


 
 

