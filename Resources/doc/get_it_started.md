# Get it started

## Install

The preferred way to install the library is using [composer](http://getcomposer.org/).
Run composer require to add dependencies to _composer.json_:

```bash
php composer.phar require "payum/payum-bundle:*@stable" "payum/xxx:*@stable"
```

_**Note**: Where payum/xxx is a payum package, for example it could be payum/paypal-express-checkout-nvp. Look at [supported payments](supported-payments.md) to find out what you can use._

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
    
payum_notify:
    resource: "@PayumBundle/Resources/config/routing/notify.xml"
```

## Configure

First we need two entities: a token and a payment details. 
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
use Payum\Core\Model\ArrayObject;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class PaymentDetails extends ArrayObject
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
        Acme\PaymentBundle\Entity\PaymentDetails: { doctrine: orm }
```

_**Note**: You should use commented path if you install payum/payum package._

## Next Step

Now you are ready to configure desired payment.
Check the list of [simple purchase examples](simple_purchase_examples.md) available.
