# Store gateway configs in a database

[Configure Payum](di/getting-started.md) registers gateways in code, so changing a credential means a
deploy. If an admin should add a gateway or change its credentials from your backend, store the configs in
a database. Payum reads a stored config the first time your code asks for that gateway and builds the
gateway from it, the same way as a gateway you register in code with `registerGateway()`. This page stores
the configs with Doctrine ORM and Payum's `GatewayConfig` model.

## Create the gateway config entity

Create an entity that extends `Payum\Core\Model\GatewayConfig`:

```php
<?php
namespace Acme\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\GatewayConfig as BaseGatewayConfig;

#[ORM\Entity]
#[ORM\Table(name: 'gateway_config')]
class GatewayConfig extends BaseGatewayConfig
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;
}
```

The `gateway_name`, `config` and `factory_name` columns come from Payum's Doctrine XML mapping for the base
class, so register that mapping next to your own, as [Storages](storages.md#create-the-entity-manager)
shows. The `config` column holds the options as JSON. The `factory_name` column is only used by 1.x
gateway factories and stays empty here.

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

The gateway name is the name you pass to `getGateway()` later. `type` names the gateway to build, and the
other options are the constructor arguments of that gateway's config class, by name. `AcmeConfig` takes
`apiKey` and `sandbox`.

## Register the stored gateways

Give the builder the storage, and list the gateway types an admin may choose from with `addGatewayType()`:

```php
<?php
// config.php

use Acme\Payum\AcmeGateway;
use Payum\Core\PayumBuilder;

/** @var \Payum\Core\Storage\StorageInterface $gatewayConfigStorage */

$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->setGatewayConfigStorage($gatewayConfigStorage)
    ->addGatewayType('acme', AcmeGateway::class)
    ->getPayum();
```

The first argument of `addGatewayType()` is the `type` value a stored config uses. A stored config can only
name a type you registered, so a row in the table cannot make your application create any class it names.
Keep the type stable once configs use it: renaming it breaks the rows that store the old name.

`getPayum()` does not read the table. Payum loads a config and builds its gateway the first time you ask
for that gateway, then reuses it for as long as that `Payum` instance lives. An application with many stored gateways, such
as one gateway per merchant, only builds the ones a request uses.

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

A name with no stored config throws `Payum\Core\Exception\InvalidArgumentException`, as it does for any
unknown gateway. A config that exists but cannot be built throws
`Payum\Core\Exception\GatewayUnavailableException`. That happens when its `type` is missing or not
registered with `addGatewayType()`, for example after you remove a gateway package, or when its options do
not construct the config class. The message names the gateway, and for bad options the config class's own
error is kept as the previous exception. Catch it where a payment page picks a gateway, so one broken
config only affects that gateway.

## Encrypt the stored credentials

The `config` column holds your PSP credentials as plain text, so anyone who can read the table or a backup
of it can use them. `CryptoStorageDecorator` wraps the storage and encrypts the options when it saves them.
See [Encrypt stored gateway configs](encrypt-gateway-configs-stored-in-database.md) to set it up.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
