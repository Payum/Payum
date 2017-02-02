# Payum Bundle. Storages

## Doctrine ORM

Add token and payment details classes:

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
# app/config/config.yml

payum:
    security:
        token_storage:
            Acme\PaymentBundle\Entity\PaymentToken: { doctrine: orm }

    storages:
        Acme\PaymentBundle\Entity\PaymentDetails: { doctrine: orm }
```

### Doctrine MongoODM.

Token use custom mongo type called `ObjectType`, so you have to add it to the kernel boot method:

```php
<?php
// app\AppKernel.php

use Doctrine\ODM\MongoDB\Types\Type;

class AppKernel extends Kernel
{
    public function boot()
    {
        Type::addType('object', 'Payum\Core\Bridge\Doctrine\Types\ObjectType');

        parent::boot();
    }
```

Now, add token and payment details classes:

```php
<?php
namespace Acme\PaymentBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\Token;

/**
 * @Mongo\Document
 */
class PaymentToken extends Token
{
}
```

```php
<?php
namespace Acme\PaymentBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\ArrayObject;

/**
 * @Mongo\Document
 */
class PaymentDetails extends ArrayObject
{
    /**
     * @Mongo\Id
     *
     * @var integer $id
     */
    protected $id;
}
```

next, you have to add mapping of the basic entities you are extended, and configure payum's storages:

```yml
# app/config/config.yml

doctrine_mongodb:
    document_managers:
        default:
            auto_mapping: true

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
            Acme\PaymentBundle\Entity\PaymentToken: { doctrine: mongodb }

    storages:
        Acme\PaymentBundle\Entity\PaymentDetails: { doctrine: mongodb }
```

_**Note**: You should use commented path if you install payum/payum package._


## Propel.

Firstly, you will need to build your schema schema. You can either use your own schema or you can copy the schema into your resources\config file from Payum\Core\Bridge\Propel\Resources\config\schema.xml
Once this has been done you will need to implement the required classes

```php
<?php

namespace Payum\Core\Bridge\Propel\Model;

use path\to\your\om\BaseToken;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\Util\Random;

class Token extends BaseToken implements TokenInterface
{
    public function __construct()
    {
        $this->setHash(Random::generateToken());
    }
}
```

```php
<?php

namespace Payum\Core\Bridge\Propel\Model;

use path\to\your\om\BasePayment;
use Payum\Core\Model\PaymentInterface;

class Payment extends BasePayment implements PaymentInterface
{
}

```

Next, you will need to add the mapping of the models you are extending, and configure payum's storages:

```yaml
payum:
    security:
        token_storage:
            Acme\DemoBundle\Model\Token: { propel1: ~ }
    storages:
        Acme\DemoBundle\Model\Payment: { propel1: ~ }
```

_**Note**: If you are using propel2 you can change propel1 to propel2._

## Custom.

We have several built in storages which cover all your needs. Sometimes you need completely custom storage.
To have a custom storage you have to implement `StorageInterface` from core:

```php
<?php
namespace Acme\PaymentBundle\Payum\Storage;

use Payum\Core\Storage\StorageInterface;

class CustomStorage implements StorageInterface
{
  // implement interface methods.
}
```

Register it as a service:

```yaml
# app/config/config.yml

services:
    acme.payment.payum.storage.custom:
        class: Acme\PaymentBundle\Payum\Storage\CustomStorage
```

When you are done you can use it like this:


```yaml
# app/config/config.yml

payum:
    storages:
        Acme\PaymentBundle\Model\Foo:
            custom: acme.payment.payum.storage.custom
```

## Filesystem.

_**Attention**: Use filesystem storage only for testing and never in production._

Add token and payment details classes:

```php
<?php
namespace Acme\PaymentBundle\Model;

use Payum\Core\Model\Token;

class PaymentToken extends Token
{
}
```

```php
<?php
namespace Acme\PaymentBundle\Model;

use Payum\Core\Model\ArrayObject;

class PaymentDetails extends ArrayObject
{
    protected $id;
}
```

next, you have to configure payum's storages:

```yaml
# app/config/config.yml

payum:
    security:
        token_storage:
            Acme\PaymentBundle\Model\PaymentToken:
                filesystem:
                    storage_dir: %kernel.root_dir%/Resources/payments
                    id_property: hash
                    
    storages:
        Acme\PaymentBundle\Model\PaymentDetails:
            filesystem:
                storage_dir: %kernel.root_dir%/Resources/payments
                id_property: id
```

* [Back to index](../index.md).

