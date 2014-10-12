# Authorize payment

In get it started we showed you an example of how to capture the payment. 
It is not always the case, sometimes you want to just authorize it and capture a bit later.
   
## Prepare order

We have to caThe only difference from capture one example 

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php

namespace Acme\PaymentBundle\Controller;

class PaymentController extends Controller 
{
    public function prepareAction() 
    {
        $paymentName = 'offline';
        
        $storage = $this->get('payum')->getStorage('Acme\PaymentBundle\Entity\Order');
        
        $order = $storage->createModel();
        $order->setNumber(uniqid());
        $order->setCurrencyCode('EUR');
        $order->setTotalAmount(123); // 1.23 EUR
        $order->setDescription('A description');
        $order->setClientId('anId');
        $order->setClientEmail('foo@example.com');
        
        $storage->updateModel($order);
        
        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName, 
            $order, 
            'done' // the route to redirect after capture;
        );
        
        return $this->redirect($captureToken->getTargetUrl())    
    }
}
```