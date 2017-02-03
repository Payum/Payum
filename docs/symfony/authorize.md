# Payum Bundle. Authorize payment

In [get it started](get-it-started.md) we showed you an example of how to capture the payment. 
It is not always the case, sometimes you want to just authorize it and capture a bit later.
   
## Prepare payment

We have to caThe only difference from capture one example 

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php

namespace Acme\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Payum\Core\Storage\StorageInterface;
use Payum\Core\Security\TokenInterface;

class PaymentController extends Controller 
{
    public function prepareAction() 
    {
        $gatewayName = 'offline';
        
        /** @var StorageInterface $storage */
        $storage = $this->get('payum')->getStorage('Acme\PaymentBundle\Entity\Payment');
        
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');
        
        $storage->update($payment);
        
        /** @var TokenInterface $captureToken */
        $captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
            $gatewayName, 
            $payment, 
            'done' // the route to redirect after capture;
        );
        
        return $this->redirect($captureToken->getTargetUrl());    
    }
}
```

* [Back to index](../index.md).