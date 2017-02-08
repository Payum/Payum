# Configure gateway in backend

In [get it started](get-it-started.md) we showed you how to configure gateways in the code. 
Sometimes you may asked to store gateways (mostly gateway credentials) to a database for example. 
So the admin can edit them in the backend. Here's the basic example how to do it in plain php. 
   
## Configure

First we have to create an entity where we store information about a gateway. 
The model must implement `Payum\Core\Model\GatewayConfigInterface`.

_**Note**: In this chapter we use DoctrineStorage._

```php
<?php
namespace Acme\Payment\Entity;

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

Now, we have to create a storage for it and build payum with gateway config storage.

```php
<?php
//config.php

use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;
use Payum\Core\Registry\DynamicRegistry;

// $objectManager is an instance of doctrine object manager.

$gatewayConfigStorage = new DoctrineStorage($objectManager, 'Acme\Payment\Entity\GatewayConfig');

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->setGatewayConfigStorage($gatewayConfigStorage)

    ->getPayum()
;
```

## Store gateway config

```php
<?php
//create_config.php

include __DIR__.'/config.php';

/** @var \Payum\Core\Storage\StorageInterface $gatewayConfigStorage */

$gatewayConfig = $gatewayConfigStorage->create();
$gatewayConfig->setGatewayName('paypal');
$gatewayConfig->setFactoryName('paypal_express_checkout_nvp');
$gatewayConfig->setConfig(array(
    'username' => 'EDIT ME',
    'password' => 'EDIT ME',
    'signature' => 'EDIT ME',
    'sandbox' => true,
));

$gatewayConfigStorage->update($gatewayConfig);
```

## Use gateway

```php
<?php
// prepare.php

include __DIR__.'/config.php';

/** @var \Payum\Core\Payum $payum */
$gateway = $payum->getGateway('paypal');
```

Back to [index](index.md).

 
 

