# Storages

Payum stores two kinds of things: your payments, and the tokens behind the payment URLs it sends a customer
to. A storage is the class that saves and loads one model class, such as your `Payment` entity. Payum ships
storages for Doctrine ORM, Doctrine MongoDB ODM and Laminas DB, plus one that writes files, and you can
write your own.

## Register a storage

Give `PayumBuilder` a storage for your tokens and one for each model class you take payments with:

```php
<?php
// config.php

use Acme\Entity\Payment;
use Acme\Entity\PaymentToken;
use Acme\Payum\AcmeConfig;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\PayumBuilder;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$payum = (new PayumBuilder())
    ->setTokenStorage(new DoctrineStorage($entityManager, PaymentToken::class))
    ->addStorage(Payment::class, new DoctrineStorage($entityManager, Payment::class))
    ->registerGateway('acme', new AcmeConfig(apiKey: 'sk_test_123', sandbox: true))
    ->getPayum();
```

With storages registered, you do not save payments by hand while a payment runs:

- `Payum::prepare()` saves the payment and issues the token that starts the payment.
- When a command arrives with a token, Payum loads the payment the token points at from its storage.
- After the command, Payum writes the PSP's state back to that payment and saves it.

Payum only saves a payment it loaded from a token. A payment you pass to a command yourself is yours to
save.

Payum finds a storage by the exact class of the model, so register it under your own class
(`Acme\Entity\Payment`), not under a class it extends. Read a storage back with `$payum->getStorage(Payment::class)`
and the token storage with `$payum->getTokenStorage()`.

{% hint style="warning" %}
Always set a token storage. Without one, Payum calls `addDefaultStorages()`, which registers file storages
for tokens and for `Payum\Core\Model\Payment`, `Payout` and `ArrayObject`, and replaces any storage you
registered for one of those three classes.
{% endhint %}

## Storages Payum ships

| Storage | Package you need | Use it for |
| --- | --- | --- |
| `Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage` | `doctrine/orm` or `doctrine/mongodb-odm` | Production, with an entity manager or a document manager. |
| `Payum\Core\Bridge\Laminas\Storage\TableGatewayStorage` | `laminas/laminas-db` | Laminas DB table gateways, with the limits listed below. |
| `Payum\Core\Storage\FilesystemStorage` | None | Tests and trying Payum out. Not for production. |
| `Payum\Core\Storage\CryptoStorageDecorator` | A cypher, such as `defuse/php-encryption` | Wraps another storage and encrypts what it saves. |

## Doctrine ORM

Payum works with Doctrine ORM 2.14 or later and 3.x, on DBAL 3 or 4:

```bash
composer require doctrine/orm symfony/cache
```

### Create the entities

Extend Payum's token and payment models:

```php
<?php
namespace Acme\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

#[ORM\Entity]
#[ORM\Table(name: 'payment_token')]
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
#[ORM\Table(name: 'payment')]
class Payment extends BasePayment
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;
}
```

Your entities add only the ID. The other columns come from Payum's XML mapping for the classes they extend,
which you register next. Payum's mappings are XML because ORM 3 has no annotation mapping; map your own
entities with attributes or XML.

### Create the entity manager

Register Payum's mapping for `Payum\Core\Model` next to the mapping for your own entities:

```php
<?php
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AttributeDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Payum\Core\Bridge\Doctrine\Types\DbalObjectType;
use Payum\Core\Payum;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

DbalObjectType::register();

$payumMapping = dirname((new ReflectionClass(Payum::class))->getFileName()) . '/Bridge/Doctrine/Resources/mapping';

$driver = new MappingDriverChain();
$driver->addDriver(new SimplifiedXmlDriver([$payumMapping => 'Payum\Core\Model']), 'Payum\Core\Model');
$driver->addDriver(new AttributeDriver([__DIR__ . '/src/Entity']), 'Acme\Entity');

$config = new Configuration();
$config->setProxyDir(sys_get_temp_dir());
$config->setProxyNamespace('Proxies');
$config->setAutoGenerateProxyClasses(true);
$config->setMetadataDriverImpl($driver);
$config->setMetadataCache(new ArrayAdapter());
$config->setQueryCache(new ArrayAdapter());
if (PHP_VERSION_ID >= 80400 && method_exists($config, 'enableNativeLazyObjects')) {
    $config->enableNativeLazyObjects(true);
}

$connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config);
$entityManager = new EntityManager($connection, $config);
```

Replace the in-memory SQLite connection with your own database. `$payumMapping` finds the mapping directory inside the installed `payum/core` package, wherever Composer put
it. If you leave the Payum mapping out, Doctrine creates tables with only an `id` column.

Call `DbalObjectType::register()` before you create the entity manager. The token mapping stores a token's
details in an `object` column, a type DBAL 4 removed. `DbalObjectType` brings it back with the same format,
so tokens stored on DBAL 3 read back unchanged. On DBAL 3 the call does nothing, so it is safe on either
version.

### Store token details as JSON

By default a token's details are stored as serialized PHP, and reading a token unserializes that column.
You can store them as JSON instead (`{"class": "Acme\\Entity\\Payment", "id": 42}`). The column is then
readable in any database tool, and no PHP is unserialized when a token is read. It takes one migration,
which converts the tokens already stored.

Install Doctrine Migrations, if your application does not use it already:

```bash
composer require doctrine/migrations
```

Add a migration that extends Payum's and lists the table of every entity that extends
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

Register the `payum_identity` type and the `StoreTokenDetailsAsJson` listener before you create the entity
manager:

```php
<?php
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Payum\Core\Bridge\Doctrine\EventListener\StoreTokenDetailsAsJson;
use Payum\Core\Bridge\Doctrine\Types\IdentityType;

IdentityType::register();

$eventManager = new EventManager();
$eventManager->addEventListener(Events::loadClassMetadata, new StoreTokenDetailsAsJson());

$entityManager = new EntityManager($connection, $config, $eventManager);
```

Register the type even though the listener registers it too. With a metadata cache, Doctrine skips the
listener once the mapping is cached, and reading a token then fails on the unknown type.

Then deploy the listener and the migration together, and run the migration.

{% hint style="warning" %}
Do not deploy one without the other. With the listener but not the migration, reading a token fails on the
serialized data. With the migration but not the listener, it fails on the JSON.
{% endhint %}

The migration works on MySQL, MariaDB, PostgreSQL and SQLite, and you can roll it back. A token whose
payment has an object ID, such as a `Uuid`, stores the ID as a string. Doctrine converts it back when it
loads the payment.

## Doctrine MongoDB ODM

Install the ODM:

```bash
composer require doctrine/mongodb-odm symfony/cache
```

Extend Payum's models as documents:

```php
<?php
namespace Acme\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Payum\Core\Model\Token;

#[ODM\Document]
class PaymentToken extends Token
{
}
```

```php
<?php
namespace Acme\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Payum\Core\Model\Payment as BasePayment;

#[ODM\Document]
class Payment extends BasePayment
{
    #[ODM\Id]
    protected $id;
}
```

Then register Payum's `object` type and mappings, and create the document manager and the storages:

```php
<?php
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AttributeDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Bridge\Doctrine\Types\ObjectType;
use Payum\Core\Payum;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

Type::addType('object', ObjectType::class);

$payumMapping = dirname((new ReflectionClass(Payum::class))->getFileName()) . '/Bridge/Doctrine/Resources/mapping';

$driver = new MappingDriverChain();
$driver->addDriver(
    new XmlDriver(new SymfonyFileLocator([$payumMapping => 'Payum\Core\Model'], '.mongodb.xml'), '.mongodb.xml'),
    'Payum\Core\Model'
);
$driver->addDriver(new AttributeDriver([__DIR__ . '/src/Document']), 'Acme\Document');

$config = new Configuration();
$config->setProxyDir(sys_get_temp_dir());
$config->setProxyNamespace('Proxies');
$config->setHydratorDir(sys_get_temp_dir());
$config->setHydratorNamespace('Hydrators');
$config->setMetadataDriverImpl($driver);
$config->setMetadataCache(new ArrayAdapter());
$config->setDefaultDB('payum');

$documentManager = DocumentManager::create(null, $config);

$paymentStorage = new DoctrineStorage($documentManager, Acme\Document\Payment::class);
$tokenStorage = new DoctrineStorage($documentManager, Acme\Document\PaymentToken::class);
```

`DocumentManager::create(null, $config)` connects to MongoDB on `localhost`. Pass a `MongoDB\Client` as the
first argument to connect elsewhere.

## Laminas DB

`TableGatewayStorage` saves models through a Laminas `TableGateway` whose result set prototype is a
`HydratingResultSet`. The hydrator turns a model into a row and back:

```php
<?php
use Acme\Entity\Payment;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\ResultSet\HydratingResultSet;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Hydrator\ReflectionHydrator;
use Payum\Core\Bridge\Laminas\Storage\TableGatewayStorage;

/** @var Adapter $adapter */

$tableGateway = new TableGateway(
    'payment',
    $adapter,
    null,
    new HydratingResultSet(new ReflectionHydrator(), new Payment())
);

$paymentStorage = new TableGatewayStorage($tableGateway, Payment::class, 'id');
```

The third argument is the name of the ID property and column, `id` by default. `ReflectionHydrator` comes
from `laminas/laminas-hydrator`.

{% hint style="warning" %}
`TableGatewayStorage` does not support `findBy()`. Its `find()` returns the Laminas result set instead of the
model, so Payum cannot load a payment from a token through it. Use Doctrine, or write your own storage,
until this is fixed.
{% endhint %}

## Filesystem

`FilesystemStorage` serializes each model into a file in a directory:

```php
<?php
use Payum\Core\Model\Payment;
use Payum\Core\Storage\FilesystemStorage;

$paymentStorage = new FilesystemStorage(__DIR__ . '/var/payum', Payment::class, 'number');
```

The third argument is the property that holds the ID. When it is empty, the storage generates one. This is
the storage `addDefaultStorages()` registers, in the system temp directory.

{% hint style="warning" %}
Do not use `FilesystemStorage` in production. It does no locking, so two requests can overwrite each other's
changes, and it does not support `findBy()`.
{% endhint %}

## Encrypt what you store

`CryptoStorageDecorator` wraps another storage. It encrypts a model when it saves it and decrypts it when it
loads it, for models that implement `Payum\Core\Security\CryptedInterface`. Payum uses it for gateway
credentials. See [Encrypt stored gateway configs](encrypt-gateway-configs-stored-in-database.md).

## Write your own storage

Extend `Payum\Core\Storage\AbstractStorage` and implement five methods. This storage keeps
`Payum\Core\Model\Payment` in a table through PDO, and uses the payment number as its ID:

```php
<?php

namespace App\Payment;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Payment;
use Payum\Core\Storage\AbstractStorage;
use Payum\Core\Storage\IdentityInterface;
use PDO;

/**
 * @extends AbstractStorage<Payment>
 */
final class PdoPaymentStorage extends AbstractStorage
{
    public function __construct(private readonly PDO $pdo)
    {
        parent::__construct(Payment::class);
    }

    public function findBy(array $criteria): array
    {
        if (array_keys($criteria) !== ['number']) {
            throw new LogicException('Payments can only be found by number.');
        }

        $payment = $this->doFind($criteria['number']);

        return null === $payment ? [] : [$payment];
    }

    protected function doFind(mixed $id): ?object
    {
        $statement = $this->pdo->prepare('SELECT * FROM payment WHERE number = ?');
        $statement->execute([$id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return false === $row ? null : $this->hydrate($row);
    }

    protected function doUpdateModel(object $model): object
    {
        $this->pdo->prepare('REPLACE INTO payment (number, total_amount, currency_code, details) VALUES (?, ?, ?, ?)')
            ->execute([
                $model->getNumber(),
                $model->getTotalAmount(),
                $model->getCurrencyCode(),
                json_encode($model->getDetails(), JSON_THROW_ON_ERROR),
            ]);

        return $model;
    }

    protected function doDeleteModel(object $model): void
    {
        $this->pdo->prepare('DELETE FROM payment WHERE number = ?')->execute([$model->getNumber()]);
    }

    protected function doGetIdentity(object $model): IdentityInterface
    {
        return new Identity($model->getNumber(), $model);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Payment
    {
        $payment = new Payment();
        $payment->setNumber($row['number']);
        $payment->setTotalAmount((int) $row['total_amount']);
        $payment->setCurrencyCode($row['currency_code']);
        $payment->setDetails(json_decode($row['details'], true, 512, JSON_THROW_ON_ERROR));

        return $payment;
    }
}
```

What each method must do:

| Method | Must |
| --- | --- |
| `doUpdateModel()` | Save the model, giving it an ID if it has none, and return it. |
| `doFind()` | Return the model with that ID, or `null`. |
| `doGetIdentity()` | Return a `Payum\Core\Model\Identity` with the model's ID. Payum stores it in the token and passes the ID back to `doFind()`. |
| `doDeleteModel()` | Delete the model. |
| `findBy()` | Return the models that match the criteria, as an array. Throw a `Payum\Core\Exception\LogicException` for criteria you do not support. |

`AbstractStorage` checks that each model is an instance of the class you pass to its constructor, and
handles `create()`, `support()` and `find()` for you. The `REPLACE INTO` statement works on SQLite and
MySQL. Use your database's upsert syntax on another one.

Register it like any other storage:

```php
<?php

use App\Payment\PdoPaymentStorage;
use Payum\Core\Model\Payment;

$builder->addStorage(Payment::class, new PdoPaymentStorage($pdo));
```

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
