# Store gateway configs in a database

[Configure Payum](di/getting-started.md) registers gateways in code, so changing a credential means a
deploy. If an admin should add a gateway or change its credentials from your backend, store the configs in
a database and register a gateway for each stored config when you build Payum.

Payum does not load gateways from a database for you. You keep the configs in a storage, read them before
calling `getPayum()`, and pass each one to `registerGateway()`. This page does that with Doctrine ORM and
Payum's `GatewayConfig` model.

## Create the gateway config entity

Create an entity that extends `Payum\Core\Model\GatewayConfig`:

```php
<?php
namespace Acme\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\GatewayConfig as BaseGatewayConfig;

#[ORM\Entity]
#[ORM\Table(name: 'gateway_config')]
#[ORM\AttributeOverrides([
    new ORM\AttributeOverride(
        name: 'factoryName',
        column: new ORM\Column(name: 'factory_name', type: 'string', nullable: true),
    ),
])]
class GatewayConfig extends BaseGatewayConfig
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;
}
```

The `gateway_name` and `config` columns come from Payum's Doctrine XML mapping for the base class, so
register that mapping next to your own, as [Storages](storages.md#create-the-entity-manager) shows. The
`config` column holds the options as JSON. The attribute override makes the `factory_name` column nullable,
because a gateway registered with `registerGateway()` has no factory name.

## Store a gateway config

Wrap the entity in a `DoctrineStorage`, then create a config, set its gateway name and options, and save it:

```php
<?php
// create_config.php

use Acme\Entity\GatewayConfig;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$gatewayConfigStorage = new DoctrineStorage($entityManager, GatewayConfig::class);

$gatewayConfig = $gatewayConfigStorage->create();
$gatewayConfig->setGatewayName('acme');
$gatewayConfig->setConfig([
    'type' => 'acme',
    'apiKey' => 'sk_test_123',
    'sandbox' => true,
]);

$gatewayConfigStorage->update($gatewayConfig);
```

The gateway name is the name you pass to `getGateway()` later. `type` picks the gateway class when you load
the config, and the other options are the constructor arguments of that gateway's config class, by name.
`AcmeConfig` takes `apiKey` and `sandbox`.

## Register the stored gateways

Before you call `getPayum()`, load every stored config and register a gateway for it:

```php
<?php
// config.php

use Acme\Payum\AcmeGateway;
use Payum\Core\PayumBuilder;

/** @var \Payum\Core\Storage\StorageInterface $gatewayConfigStorage */

$gatewayTypes = [
    'acme' => AcmeGateway::class,
];

$builder = new PayumBuilder();

foreach ($gatewayConfigStorage->findBy([]) as $storedConfig) {
    $options = $storedConfig->getConfig();
    $gateway = new ($gatewayTypes[$options['type']])();
    $configClass = $gateway->configClass();

    unset($options['type'], $options['encrypted']);

    $builder->registerGateway($storedConfig->getGatewayName(), new $configClass(...$options));
}

$payum = $builder->getPayum();
```

`$gatewayTypes` lists the gateways an admin may choose from. Keep this list in code rather than storing a
class name in the database, so a row in the table cannot make your application create any class it names.
Each gateway's `configClass()` names its config class, which is constructed from the stored options. A
missing or misspelled option fails here with an `Error` that names the argument. The `encrypted` key is
only present when you [encrypt the configs](encrypt-gateway-configs-stored-in-database.md).

Add the token and payment storages to the builder as usual. Gateways registered in code with
`registerGateway()` work next to the stored ones.

## Edit a stored gateway config

Find the config by its gateway name, change the options and save it again. This is what an admin form does:

```php
<?php
// edit_config.php

/** @var \Payum\Core\Storage\StorageInterface $gatewayConfigStorage */

[$gatewayConfig] = $gatewayConfigStorage->findBy(['gatewayName' => 'acme']);

$gatewayConfig->setConfig(array_replace($gatewayConfig->getConfig(), [
    'sandbox' => false,
]));

$gatewayConfigStorage->update($gatewayConfig);
```

`setConfig()` replaces all the options, so merge the new values into the current ones. The change applies
the next time you build Payum, which in a PHP application served per request is the next request.

To build the form for a gateway that has no config yet, create the gateway and read `configClass()`. The
constructor parameters of that class are the fields the admin fills in.

## Use the gateway

Get a stored gateway by its name, the same way as a gateway registered in code:

```php
<?php
// prepare.php

/** @var \Payum\Core\Payum $payum */

$gateway = $payum->getGateway('acme');
```

## Encrypt the stored credentials

The `config` column holds your PSP credentials as plain text, so anyone who can read the table or a backup
of it can use them. `CryptoStorageDecorator` wraps the storage and encrypts the options when it saves them.
See [Encrypt stored gateway configs](encrypt-gateway-configs-stored-in-database.md) to set it up.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
