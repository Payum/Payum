# Get it started

## Install

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/payum-bundle:*@stable" "payum/offline:*@stable"
```

_**Note**: Where payum/offline is a php payum extension, you can for example change it to payum/paypal-express-checkout-nvp or payum/stripe. Look at [supported payments](https://github.com/Payum/Core/blob/master/Resources/docs/supported-payments.md) to find out what you can use._

_**Note**: Use payum/payum if you want to install all payments at once._

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
# app/config/routing.yml

payum_capture:
    resource: "@PayumBundle/Resources/config/routing/capture.xml"
    
payum_authorize:
    resource: "@PayumBundle/Resources/config/routing/notify.xml"
    
payum_notify:
    resource: "@PayumBundle/Resources/config/routing/notify.xml"
```

## Configure

First we need two entities: a token and an order. 
The token entity is used to protect your payments where the second one stores all your payment information.

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
use Payum\Core\Model\Order as BaseOrder;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Order extends BaseOrder
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

next, you have to add mapping of the basic entities you are extended, and configure payum's storages:

```yml
#app/config/config.yml

twig:
    paths:
        %kernel.root_dir%/../vendor/payum/payum/src/Payum/Core/Resources/views: PayumCore

doctrine:
    orm:
        entity_managers:
            default:
                mappings:
                    payum:
                        is_bundle: false
                        type: xml
                        dir: %kernel.root_dir%/../vendor/payum/core/Payum/Core/Bridge/Doctrine/Resources/mapping

                        # set this dir instead if you use `payum/payum` library
                        #dir: %kernel.root_dir%/../vendor/payum/payum/src/Payum/Core/Bridge/Doctrine/Resources/mapping

                        prefix: Payum\Core\Model

payum:
    security:
        token_storage:
            Acme\PaymentBundle\Entity\PaymentToken: { doctrine: orm }

    storages:
        Acme\PaymentBundle\Entity\Order: { doctrine: orm }
            
    contexts:
        offline:
            offline: ~
```

_**Note**: You should use commented path if you install payum/payum package._

_**Note**: You can add other payments to the contexts too._

## Prepare order

At this stage we have to create an order. Add some information into it. 
Create a capture token and delegate the job to capture action.

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

## Payment is done

After the capture did its job you will be redirected to done action. 
One we set while token creation in prepare action.
You can read more about it in the dedicated [chapter](purchase_done_action.md)
The capture action script always redirects you to done one, no matter the payment was successful or not.
In done action we may check the payment status, update the model, dispatch events and so on.

```php
<?php
// src/Acme/PaymentBundle/Controller/PaymentController.php
namespace Acme\PaymentBundle\Controller;

use Payum\Core\Request\GetHumanStatus;

class PaymentController extends Controller 
{
    public function doneAction()
    {
        include 'config.php';
        
        $token = $requestVerifier->verify($_REQUEST);
        // $requestVerifier->invalidate($token);
        
        $payment = $payum->getPayment($token->getPaymentName());
        
        $payment->execute($status = new GetHumanStatus($token));
        
        return new JsonResponse(array(
            'status' => $status->getValue(),
            'order' => array(
                'client' => array(
                    'id' => $status->getModel()->getClientId(),
                    'email' => $status->getModel()->getClientEmail(),
                ),
                'number' => $status->getModel()->getNumber(),
                'description' => $status->getModel()->getCurrencyCode(),
                'total_amount' => $status->getModel()->getTotalAmount(),
                'currency_code' => $status->getModel()->getCurrencyCode(),
                'details' => $status->getModel()->getDetails(),
            ),
        ));
    }
```

## Next Step

* [Payment configurations](configuration_reference.md)
