# Get started

## Payum Bundle

### Install

The preferred way to install the library is using [composer](http://getcomposer.org/). Run `composer require` to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/payum-bundle" "payum/offline" "php-http/guzzle7-adapter"
```

{% hint style="info" %}
_**Note**: Where **payum/offline** is a payum gateway, you can for example change it to **payum/paypal-express-checkout-nvp** or **payum/stripe**. Look at_ [_supported gateways_](../supported-gateways.md) _to find out what you can use._
{% endhint %}

{% hint style="info" %}
_**Note**: Use **payum/payum** if you want to install all gateways at once._
{% endhint %}

When using Symfony Flex, the bundle should automatically be added to the bundle config.

If that did not happen, or if you are not using Symfony Flex, then enable the bundle in the config

```php
<?php
// config/bundles.php

return [
    Payum\Bundle\PayumBundle\PayumBundle::class => ['all' => true],
];
```

Now let's import Payum's routes:

```yaml
# config/routes.yml

payum_all:
    resource: "@PayumBundle/Resources/config/routing/all.xml"
```

### Configure

First we need two entities: `Token` and `Payment`.\
The token entity is used to protect your payments, while the payment entity stores all your payment information.

_**Note**: In this chapter we show how to use [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html) entities. There are other supported [storages](storages.md)._

```php
<?php
// src/Entity/PaymentToken.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class PaymentToken extends Token
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;
}
```

```php
<?php
// src/Entity/Payment.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class Payment extends BasePayment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;
}
```

Next, you have to add mapping information, and configure payum's storages:

```yml
# config/packages/payum.yaml

payum:
    security:
        token_storage:
            App\Entity\PaymentToken: { doctrine: orm }

    storages:
        App\Entity\Payment: { doctrine: orm }
            
    gateways:
        offline:
            factory: offline
```

_**Note**: You can add other gateways to the `gateways` key too._

## Create payment and redirect the user

Now we can create a payment:

```php
<?php
// src/Controller/PaymentController.php

namespace Acme\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Payum\Core\Payum;
use Payum\Core\Request\GetHumanStatus;
use App\Entity\Payment;

class PaymentController extends AbstractController
{
    /**
     * @Route("/prepare-payment", name="payum_prepare_payment")
     */
    public function prepare(Payum $payum) 
    {
        $gatewayName = 'offline';
        
        $storage = $payum->getStorage(Payment::class);
        
        $payment = $storage->create();
        $payment->setNumber(uniqid());
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123); // 1.23 EUR
        $payment->setDescription('A description');
        $payment->setClientId('anId');
        $payment->setClientEmail('foo@example.com');
        
        $storage->update($payment);
        
        $captureToken = $payum->getTokenFactory()->createCaptureToken(
            $gatewayName, 
            $payment, 
            'payum_payment_done' // the route to redirect after capture
        );
        
        return $this->redirect($captureToken->getTargetUrl());    
    }
}
```

### Payment is done

After setting up the payment, the user will be redirected to `doneAction()`. You can read more about it in its dedicated [chapter](purchase-done-action.md). `doneAction()` is always called, no matter if the payment was successful or not. Here we may check the payment status, update the model, dispatch events and so on.

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php
namespace Acme\PaymentBundle\Controller;

use Payum\Core\Request\GetHumanStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentController extends Controller 
{
    /**
     * @Route("/payment-done", name="payum_payment_done")
     */
    public function doneAction(Request $request, Payum $payum)
    {
        $token = $payum->getHttpRequestVerifier()->verify($request);
        
        $gateway = $payum->getGateway($token->getGatewayName());
        
        // You can invalidate the token, so that the URL cannot be requested any more:
        // $payum->getHttpRequestVerifier()->invalidate($token);
        
        // Once you have the token, you can get the payment entity from the storage directly. 
        // $identity = $token->getDetails();
        // $payment = $payum->getStorage($identity->getClass())->find($identity);
        
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

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/Payum)
