# Configure payment in backend

In [get it started](get_it_started.md) we showed you how to configure payments in the Symfony config.yml file. 
Though it covers most of the cases sometimes you may want to configure payments in the backend. 
For example you will be able to change a gateway credentials, add or delete a payment.
As a backend we use [Sonata Admin](http://sonata-project.org/bundles/admin/2-3/doc/index.html) bundle. 
Follow its doc to configure it properly 
   
## Configure

First we have to create an entity where we store information about a payment. 
The model must implement `Payum\Core\Model\PaymentConfigInterface`.

_**Note**: In this chapter we show how to use Doctrine ORM entities. There are other supported [storages](storages.md)._

```php
<?php
namespace Acme\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\PaymentConfig as BasePaymentConfig;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class PaymentConfig extends BasePaymentConfig
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
    dynamic_payments:
        sonata_admin: true
        config_storage: 
            Acme\PaymentBundle\Entity\PaymentConfig: { doctrine: orm }
```

## Backend

Once you have configured everything doctrine, payum and sonata admin go to `/admin/dashboard`. 
There you have to see a `Payments` section. Try to add a payment there.

## Use payment

Let's say you created a payment with name `paypal`. Here we will show you how to use it.

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php

namespace Acme\PaymentBundle\Controller;

class PaymentController extends Controller 
{
    public function prepareAction() 
    {
        $paymentName = 'paypal';
        
        $storage = $this->get('payum')->getStorage('Acme\PaymentBundle\Entity\Order');
        
        $order = $storage->create();
        $order->setNumber(uniqid());
        $order->setCurrencyCode('EUR');
        $order->setTotalAmount(123); // 1.23 EUR
        $order->setDescription('A description');
        $order->setClientId('anId');
        $order->setClientEmail('foo@example.com');
        
        $storage->update($order);
        
        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName, 
            $order, 
            'done' // the route to redirect after capture
        );
        
        return $this->redirect($captureToken->getTargetUrl());    
    }
}
```

_**Note**: If you configured a payment in config.yml and in the backend with same name. Backend one will be used._


 
 

