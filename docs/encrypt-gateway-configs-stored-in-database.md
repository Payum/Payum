# Encrypt stored gateway configs

When you [store gateway configs in a database](configure-gateway-in-backend.md), the credentials for your
payment service provider (PSP) are saved as plain text. Anyone who can read the table or a backup of it can
call the PSP's API as you. Payum's `CryptoStorageDecorator` wraps the gateway config storage. It encrypts
each option when it saves a config and decrypts it when it loads one.

This page builds on the Doctrine setup in [Store gateway configs in a database](configure-gateway-in-backend.md).

## Install the encryption library

Payum encrypts with [defuse/php-encryption](https://github.com/defuse/php-encryption). Install it:

```bash
composer require defuse/php-encryption:^2
```

## Generate a key

Generate a key once, with the command the library installs:

```bash
vendor/bin/generate-defuse-key
```

It prints a single line that starts with `def0000`. Store it outside your code, for example in an
environment variable that your deployment sets:

```dotenv
PAYUM_CYPHER_KEY=def00000c794ad36e544b9557c098620e19be5f96529a227d4b22874ce16c9cab2ae90a45b07f20b86349c6e1d892ed042562f86ebb50fbb8b6394b8797c63b12d232db4
```

Every request must use the same key. Do not generate the key in your application code.

{% hint style="warning" %}
Keep a backup of the key somewhere other than the database. If you lose it, nobody can decrypt the stored
configs, and you have to enter every gateway's credentials again.
{% endhint %}

## Wrap the gateway config storage

Create a `DefuseCypher` with the key, and pass it with your Doctrine storage to `CryptoStorageDecorator`:

```php
<?php
// config.php

use Acme\Entity\GatewayConfig;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Storage\CryptoStorageDecorator;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$gatewayConfigStorage = new CryptoStorageDecorator(
    new DoctrineStorage($entityManager, GatewayConfig::class),
    new DefuseCypher(getenv('PAYUM_CYPHER_KEY'))
);
```

Use this decorated storage everywhere the other page uses `$gatewayConfigStorage`: to save configs, to edit
them and to register the stored gateways. `Acme\Entity\GatewayConfig` extends `Payum\Core\Model\GatewayConfig`,
which already implements `Payum\Core\Security\CryptedInterface`, the interface the decorator requires. You do
not change the entity.

## Store a gateway config

Create and save configs through the decorated storage, the same way as without encryption:

```php
<?php
// create_config.php

/** @var \Payum\Core\Storage\StorageInterface $gatewayConfigStorage */

$gatewayConfig = $gatewayConfigStorage->create();
$gatewayConfig->setGatewayName('acme');
$gatewayConfig->setConfig([
    'type' => 'acme',
    'apiKey' => 'sk_test_123',
    'sandbox' => true,
]);

$gatewayConfigStorage->update($gatewayConfig);
```

The `config` column now holds a ciphertext for each string option, plus an `encrypted` flag:

```json
{"type":"def50200a3f1...","apiKey":"def502007c2e...","sandbox":true,"encrypted":true}
```

The gateway name stays readable, so you can still find a config by name.

## Register the stored gateways

Load the configs through the decorated storage and register them, with the loop from
[Store gateway configs in a database](configure-gateway-in-backend.md#register-the-stored-gateways). The
decorator decrypts each config as it loads it, so `getConfig()` returns the plain options. The decorator
adds `'encrypted' => true` to them, which is why that loop removes the `encrypted` key before it builds the
config object. An admin form that loads a config this way also shows the plain values.

## Which values you can store

The decorator encrypts every string option. It stores booleans as they are, so `'sandbox' => true` works.
Any other type stops the save with a `TypeError`, including integers, floats, `null` and nested arrays.
Store a number as a string (`'timeout' => '30'`) and leave out an option instead of setting it to `null`.
A config class that takes an `int` then receives a string, which PHP accepts only when the file that
constructs it does not declare `strict_types`, so prefer config classes that take strings or booleans.

## Always read and write through the decorated storage

Only the decorator decrypts. If you load a config some other way, for example with a Doctrine repository,
`getConfig()` returns an empty array. If you save one some other way, its new values go to the database as
plain text. Inject the decorated storage wherever your code reads or edits gateway configs, such as an
admin form.

If you load a config with a different key from the one that encrypted it, the decorator throws
`Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException`.

## Encrypt configs you have already stored

Configs saved before you added the decorator keep working. The decorator sees they have no `encrypted` flag
and returns them as they are. They stay in plain text until you save them again.

To encrypt them all at once, run a script that loads each config through the decorated storage, sets its
options again and saves it:

```php
<?php
// encrypt_existing_configs.php

/** @var \Payum\Core\Storage\StorageInterface $gatewayConfigStorage */

foreach ($gatewayConfigStorage->findBy([]) as $gatewayConfig) {
    $gatewayConfig->setConfig($gatewayConfig->getConfig());

    $gatewayConfigStorage->update($gatewayConfig);
}
```

You can run the script more than once. A config that is already encrypted is decrypted on load and
encrypted again on save.

{% hint style="warning" %}
Do not skip the `setConfig()` call. Without it, the decorator marks a plain-text config as encrypted but
leaves its values as they were, and loading it afterwards fails.
{% endhint %}

## Advanced: change the key

To replace the key, load every config with a decorator that uses the old key and save it with one that uses
the new key:

```php
<?php
use Acme\Entity\GatewayConfig;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Storage\CryptoStorageDecorator;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$oldStorage = new CryptoStorageDecorator(
    new DoctrineStorage($entityManager, GatewayConfig::class),
    new DefuseCypher(getenv('PAYUM_CYPHER_KEY_OLD'))
);
$newStorage = new CryptoStorageDecorator(
    new DoctrineStorage($entityManager, GatewayConfig::class),
    new DefuseCypher(getenv('PAYUM_CYPHER_KEY'))
);

foreach ($oldStorage->findBy([]) as $gatewayConfig) {
    $newStorage->update($gatewayConfig);
}
```

Deploy the new key at the same time as you run the script, because requests that still use the old key
cannot read the re-encrypted configs.

## Advanced: use your own model or cypher

`CryptoStorageDecorator` works with any model that implements `Payum\Core\Security\CryptedInterface`. Its
`encrypt()` and `decrypt()` methods receive a `Payum\Core\Security\CypherInterface` and decide which fields
to change. Implement `CypherInterface` yourself to encrypt with a different library or with a key management
service. Its `encrypt()` and `decrypt()` methods take and return strings.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
