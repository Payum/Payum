# Configure payment in backend

In [get it started](get_it_started.md) we showed you how to configure payments in the code. 
Sometimes you may asked to store payments (mostly gateway credentials) to a database for example. 
So the admin can edit them in the backend. Here's the basic example how to do it in plain php. 
   
## Configure

First we have to create an entity where we store information about a payment. 
The model must implement `Payum\Core\Model\GatewayConfigInterface`.

_**Note**: In this chapter we use DoctrineStorage._

```php
<?php
namespace Acme\Payment\Entity;

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

Now, we have to create a storage for it and change the simple registry with dynamic one.

```php
<?php
//config.php

// ...

use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Registry\DynamicRegistry;

// $objectManager is an instance of doctrine object manager.

$paymentConfigStorage = new DoctrineStorage($objectManager, 'Acme\Payment\Entity\PaymentConfig');

$payum = new DynamicRegistry($paymentConfigStorage, $payum);
```

## Store payment config

```php
<?php
//create_config.php

include 'config.php';

$paymentConfig = $paymentConfigStorage->create();
$paymentConfig->setPaymentName('paypal');
$paymentConfig->setFactoryName('paypal_express_checkout_nvp');
$paymentConfig->setConfig(array(
    'username' => 'EDIT ME',
    'password' => 'EDIT ME',
    'signature' => 'EDIT ME',
    'sandbox' => true,
));

$paymentConfigStorage->update($paymentConfig);
```

## Use payment

```php
<?php
// prepare.php

include 'config.php';

$payment = $payum->getPayment('paypal');
```

Back to [index](index.md).

 
 

