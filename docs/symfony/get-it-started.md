<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Payum Bundle. Get it started

## Install

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run `composer require` to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/payum-bundle" "payum/offline" "php-http/guzzle6-adapter"
```

_**Note**: Where payum/offline is a php payum extension, you can for example change it to payum/paypal-express-checkout-nvp or payum/stripe. Look at [supported gateways](../supported-gateways.md) to find out what you can use._

_**Note**: Use payum/payum if you want to install all gateways at once._

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Payum\Bundle\PayumBundle\PayumBundle(),
    );
}
```

So now after you registered the bundle let's import routing.

```yaml
# app/config/routing.yml (In Symfony 4 & 5 config/routes.yaml )

payum_all:
    resource: "@PayumBundle/Resources/config/routing/all.xml"
```

## Configure

First we need two entities: `Token` and `Payment`.  
The token entity is used to protect your payments, while the payment entity stores all your payment information.

_**Note**: In this chapter we show how to use Doctrine ORM entities. There are other supported [storages](storages.md)._

```php
<?php
namespace Acme\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class PaymentToken extends Token
{
}
```

```php
<?php
namespace Acme\PaymentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Payment extends BasePayment
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

next, you have to add mapping information, and configure payum's storages:

```yml
#app/config/config.yml

payum:
    security:
        token_storage:
            Acme\PaymentBundle\Entity\PaymentToken: { doctrine: orm }

    storages:
        Acme\PaymentBundle\Entity\Payment: { doctrine: orm }
            
    gateways:
        offline:
            factory: offline
```

_**Note**: You can add other gateways to the gateways section too._

## Prepare order

Now we can create an order. In the last line the user is redirected to an URL which is handled by [`CaptureController ::doAction()`](https://github.com/Payum/PayumBundle/blob/fd930cb9516c8a5f19b4eeae35c8e37eea77ce11/Controller/CaptureController.php#L30)

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php

namespace Acme\PaymentBundle\Controller;

class PaymentController extends Controller 
{
    public function prepareAction() 
    {
        $gatewayName = 'offline';
        
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

## Payment is done

After setting up the payment, the user will be redirected to `doneAction()`. 
You can read more about it in its dedicated [chapter](purchase-done-action.md).
`doneAction()` is always called, no matter if the payment was successful or not.
Here we may check the payment status, update the model, dispatch events and so on.

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php
namespace Acme\PaymentBundle\Controller;

use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentController extends Controller 
{
    public function doneAction(Request $request)
    {
        $token = $this->get('payum')->getHttpRequestVerifier()->verify($request);
        
        $gateway = $this->get('payum')->getGateway($token->getGatewayName());
        
        // You can invalidate the token, so that the URL cannot be requested any more:
        // $this->get('payum')->getHttpRequestVerifier()->invalidate($token);
        
        // Once you have the token, you can get the payment entity from the storage directly. 
        // $identity = $token->getDetails();
        // $payment = $this->get('payum')->getStorage($identity->getClass())->find($identity);
        
        // Or Payum can fetch the entity for you while executing a request (preferred).
        $gateway->execute($status = new GetHumanStatus($token));
        $payment = $status->getFirstModel();
        
        // Now you have order and payment status
        
        return new JsonResponse(array(
            'status' => $status->getValue(),
            'payment' => array(
                'total_amount' => $payment->getTotalAmount(),
                'currency_code' => $payment->getCurrencyCode(),
                'details' => $payment->getDetails(),
            ),
        ));
    }
}
```

* [Back to index](../index.md).
