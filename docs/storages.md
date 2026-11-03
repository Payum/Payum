# Storages

Storage allow you save,fetch payment related information. They could be used explicitly, it means you have to call save or fetch methods when it is required. Or you can integrate a storage to a gateway using `StorageExtension`. In this case every time gateway finish to execute a request it stores the information. `StorageExtension` could also load a model by it is `Identificator` so you do not have to care about that.

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

### Doctrine ORM

Payum works with Doctrine ORM 2.14 or later and 3.x, on DBAL 3 or 4.

```
php composer.phar require "doctrine/orm"
```

Add token and payment classes:

```php
<?php
namespace Acme\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

#[ORM\Entity]
#[ORM\Table]
class PaymentToken extends Token
{
}
```

```php
<?php
namespace Acme\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

#[ORM\Entity]
#[ORM\Table]
class Payment extends BasePayment
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;
}
```

Next, create an entity manager and Payum's storages:

```php
<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Bridge\Doctrine\Types\DbalObjectType;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

DbalObjectType::register();

$driver = new MappingDriverChain();

// payum's basic models
$driver->addDriver(
    new SimplifiedXmlDriver(['path/to/Payum/Core/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model']),
    'Payum\Core\Model'
);

// your models
$driver->addDriver(new AttributeDriver(['path/to/Acme/Entity'], true), 'Acme\Entity');

$config = new Configuration();
$config->setAutoGenerateProxyClasses(true);
$config->setProxyDir(\sys_get_temp_dir());
$config->setProxyNamespace('Proxies');
$config->setMetadataDriverImpl($driver);
if (\PHP_VERSION_ID >= 80400 && method_exists($config, 'enableNativeLazyObjects')) {
    $config->enableNativeLazyObjects(true);
}
$config->setQueryCache(new ArrayAdapter());
$config->setMetadataCache(new ArrayAdapter());

$connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config);
$entityManager = new EntityManager($connection, $config);

$paymentStorage = new DoctrineStorage($entityManager, 'Acme\Entity\Payment');
$tokenStorage = new DoctrineStorage($entityManager, 'Acme\Entity\PaymentToken');
```

Call `DbalObjectType::register()` before the first entity manager is created. It does nothing on DBAL 3,
which still has the `object` type, so it is safe to call on either version.

#### Storing token details as JSON

By default a token's details are stored as serialized PHP, and reading a token unserializes that column. You
can store them as JSON instead (`{"class": "Acme\\Entity\\Payment", "id": 42}`). The column is then readable
in any database tool, and no PHP is unserialized when a token is read. It takes one migration, which
converts the rows already stored. Tokens issued before the migration keep working.

1. Install Doctrine Migrations, if your application does not use it already:

   ```
   php composer.phar require "doctrine/migrations"
   ```

2. Add a migration that extends Payum's and lists the table of every entity that extends
   `Payum\Core\Model\Token`:

   ```php
   <?php
   namespace DoctrineMigrations;

   use Payum\Core\Bridge\Doctrine\Migrations\AbstractTokenDetailsToJsonMigration;

   final class Version20261103120000 extends AbstractTokenDetailsToJsonMigration
   {
       protected function tokenTables(): array
       {
           return ['payment_token'];
       }
   }
   ```

3. Register the `StoreTokenDetailsAsJson` listener on the entity manager:

   ```php
   <?php
   use Doctrine\Common\EventManager;
   use Doctrine\ORM\EntityManager;
   use Doctrine\ORM\Events;
   use Payum\Core\Bridge\Doctrine\EventListener\StoreTokenDetailsAsJson;

   $eventManager = new EventManager();
   $eventManager->addEventListener(Events::loadClassMetadata, new StoreTokenDetailsAsJson());

   $entityManager = new EntityManager($connection, $config, $eventManager);
   ```

4. Deploy both together and run the migration. **Do not run one without the other:** with the listener but not
   the migration, reading a token fails on the serialized data; with the migration but not the listener, it
   fails on the JSON.

The migration works on MySQL, MariaDB, PostgreSQL and SQLite, and can be rolled back. A token whose id is an
object, such as a `Uuid`, stores the id as a string; Doctrine converts it back when the payment is loaded.

#### Doctrine MongoODM.

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

### Filesystem.

```php
<?php
use Payum\Core\Storage\FilesystemStorage;

$storage = new FilesystemStorage(
    '/path/to/storage', 
    'Payum\Core\Model\Payment', 
    'number'
);
```

### Custom.

You can create your own custom storage. To do so just implement `StorageInterface`.

```php
<?php
use Payum\Core\Storage\StorageInterface;

class CustomStorage implements StorageInterface
{
    // implement all declared methods
}
```

### TODO

* [Pdo](http://php.net/manual/en/book.pdo.php) Storage - https://github.com/Payum/Payum/issues/205
* [Yii ActiveRecord](http://www.yiiframework.com/doc/guide/1.1/en/database.ar) Storage - https://github.com/Payum/PayumYiiExtension/pull/4

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
