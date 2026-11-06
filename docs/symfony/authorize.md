# Authorize Payment

In [get it started](get-it-started.md) we showed you an example of how to capture the payment. It is not always the case, sometimes you want to just authorize it and capture a bit later.

### Prepare payment

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
        $captureToken = $this->get('payum')->getTokenFactory()->createAuthorizeToken(
            $gatewayName, 
            $payment, 
            'done' // the route to redirect after autorize;
        );
        
        return $this->redirect($captureToken->getTargetUrl());    
    }
}
```

### Authorize, then capture

If the route after authorizing is another Payum operation, such as `payum_capture_do`, tell Payum where that
operation sends the customer when it is done. Otherwise it has nowhere to go, and the capture fails with an
error naming its token. Payum issues a token for that last route too, so a
[done action](purchase-done-action.md) that verifies the token works unchanged.

```php
use Payum\Core\Security\TokenPurpose;

$token = $this->get('payum')->getTokenIssuer()->create(
    TokenPurpose::Authorize,
    $gatewayName,
    $payment,
    'payum_capture_do', // runs after authorizing
    [],
    'done' // where the capture sends the customer
);

return $this->redirect($token->getTargetUrl());
```

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
