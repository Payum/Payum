<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Storages

Storage allow you save,fetch payment related information. 
They could be used explicitly, it means you have to call save or fetch methods when it is required. 
Or you can integrate a storage to a gateway using `StorageExtension`. 
In this case every time gateway finish to execute a request it stores the information. 
`StorageExtension` could also load a model by it is `Identificator` so you do not have to care about that.

Explicitly used example:

```php
<?php
use Payum\Core\Storage\FilesystemStorage;

$storage = new FilesystemStorage('/path/to/storage', 'Payum\Core\Model\Payment', 'number');

$order = $storage->create();
$order->setTotalAmount(123);
$order->setCurrency('EUR');

$storage->update($order);

$foundOrder = $storage->find($order->getNumber());
```

Implicitly used example: 

```php
<?php
use Payum\Core\Extension\StorageExtension;
use Payum\Core\Gateway;
use Payum\Core\Storage\FilesystemStorage;

$gateway->addExtension(new StorageExtension(
   new FilesystemStorage('/path/to/storage', 'Payum\Core\Model\Payment', 'number')
));
```

Usage of a model identity with the extension:

```php
<?php
use Payum\Core\Extension\StorageExtension;
use Payum\Core\Storage\FilesystemStorage;
use Payum\Core\Request\Capture;

$storage = new FilesystemStorage('/path/to/storage', 'Payum\Core\Model\Payment', 'number');

$order = $storage->create();
$storage->update($order);

/** @var \Payum\Core\Gateway $gateway */
$gateway->addExtension(new StorageExtension($storage));

$gateway->execute($capture = new Capture(
    $storage->identify($order)
));

echo get_class($capture->getModel());
// -> Payum\Core\Model\Payment
```

## Doctrine ORM

```
php composer.phar install "doctrine/orm"
```

Add token and order classes:

```php
<?php
namespace Acme\Entity;

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
namespace Acme\Entity;

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

next, you have to create an entity manager and Payum's storage:

```php
<?php
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;

$config = new Configuration();
$driver = new MappingDriverChain;

// payum's basic models
$driver->addDriver(
    new SimplifiedXmlDriver(array('path/to/Payum/Core/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model')), 
    'Payum\Core\Model'
);

// your models
$driver->addDriver(
    $config->newDefaultAnnotationDriver(array('path/to/Acme/Entity'), false), 
    'Acme\Entity'
);

$config->setAutoGenerateProxyClasses(true);
$config->setProxyDir(\sys_get_temp_dir());
$config->setProxyNamespace('Proxies');
$config->setMetadataDriverImpl($driver);
$config->setQueryCacheImpl(new ArrayCache());
$config->setMetadataCacheImpl(new ArrayCache());

$connection = array('driver' => 'pdo_sqlite', 'path' => ':memory:');

$orderStorage = new DoctrineStorage(
   EntityManager::create($connection, $config),
   'Payum\Entity\Payment'
);

$tokenStorage = new DoctrineStorage(
   EntityManager::create($connection, $config),
   'Payum\Entity\PaymentToken'
);
```

### Doctrine MongoODM.

```
php composer.phar require "doctrine/mongodb-odm:^2.1"
```

```php
<?php
namespace Acme\Document;

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
namespace Acme\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as Mongo;
use Payum\Core\Model\Payment as BasePayment;

/**
 * @Mongo\Document
 */
class Payment extends BasePayment
{
    /**
     * @Mongo\Id
     *
     * @var integer $id
     */
    protected $id;
}
```

next, you have to create an entity manager and Payum's storage:

```php
<?php
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;

Type::addType('object', 'Payum\Core\Bridge\Doctrine\Types\ObjectType');

$driver = new MappingDriverChain;

// payum's basic models
$driver->addDriver(
    new XmlDriver(
       new SymfonyFileLocator(array(
            '/path/to/Payum/Core/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model'
        ), '.mongodb.xml'),
        '.mongodb.xml'
    ), 
    'Payum\Core\Model'
);

// your models
$driver->addDriver(
    new AnnotationDriver(new AnnotationReader(), array(
        'path/to/Acme/Document',
    )), 
    'Acme\Document'
);

$config = new Configuration();
$config->setProxyDir(\sys_get_temp_dir());
$config->setProxyNamespace('Proxies');
$config->setHydratorDir(\sys_get_temp_dir());
$config->setHydratorNamespace('Hydrators');
$config->setMetadataDriverImpl($driver);
$config->setMetadataCacheImpl(new ArrayCache());
$config->setDefaultDB('payum_tests');

$documentManager = DocumentManager::create(null, $config);

$orderStorage = new DoctrineStorage($documentManager, 'Acme\Document\Payment');
$tokenStorage = new DoctrineStorage($documentManager, 'Acme\Document\SecurityToken');
```        

## Filesystem.

```php
<?php
use Payum\Core\Storage\FilesystemStorage;

$storage = new FilesystemStorage(
    '/path/to/storage', 
    'Payum\Core\Model\Payment', 
    'number'
);
```

## Custom.

You can create your own custom storage. To do so just implement `StorageInterface`.

```php
<?php
use Payum\Core\Storage\StorageInterface;

class CustomStorage implements StorageInterface
{
    // implement all declared methods
}
```

## TODO

* [Pdo](http://php.net/manual/en/book.pdo.php) Storage - https://github.com/Payum/Payum/issues/205
* [Yii ActiveRecord](http://www.yiiframework.com/doc/guide/1.1/en/database.ar) Storage - https://github.com/Payum/PayumYiiExtension/pull/4

* [Back to index](index.md).
