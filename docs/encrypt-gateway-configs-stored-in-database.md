# Encrypt stored gateway configs

When you [store gateway configs in a database](configure-gateway-in-backend.md), the credentials for your
payment service provider (PSP) are saved as plain text. Anyone who can read the table or a backup of it can
call the PSP's API as you. Payum's `CryptoStorageDecorator` wraps the gateway config storage. It encrypts
each option when it saves a config and decrypts it when it loads one.

This page builds on the Doctrine setup in [Store gateway configs in a database](configure-gateway-in-backend.md).

## Choose an encryption library

Payum ships a cypher (the class that encrypts and decrypts each value) for two libraries:

| Library | Payum cypher | When to use it |
| --- | --- | --- |
| [paragonie/halite](https://github.com/paragonie/halite) | `Payum\Core\Bridge\Halite\Security\HaliteCypher` | New projects. It is maintained and uses PHP's built-in `sodium` extension. |
| [defuse/php-encryption](https://github.com/defuse/php-encryption) | `Payum\Core\Bridge\Defuse\Security\DefuseCypher` | Projects that already encrypt with it. The library only receives maintenance fixes. |

The two libraries cannot read each other's ciphertext. Pick one, and if you already store configs encrypted
with defuse/php-encryption, follow [Switch to another library](#change-the-key-or-switch-to-another-library)
to move them over. Halite is licensed under the MPL-2.0, defuse/php-encryption under the MIT license.

## Install the library and generate a key

Generate the key once, outside your application, and store it outside your code, for example in an
environment variable that your deployment sets. Every request must use the same key.

{% hint style="warning" %}
Keep a backup of the key somewhere other than the database. If you lose it, nobody can decrypt the stored
configs, and you have to enter every gateway's credentials again.
{% endhint %}

### Halite

Install the library:

```bash
composer require paragonie/halite:^5.1
```

Halite has no key command, so print a new key with a one-line PHP script:

```bash
php -r 'require "vendor/autoload.php"; echo ParagonIE\Halite\KeyFactory::export(ParagonIE\Halite\KeyFactory::generateEncryptionKey())->getString(), PHP_EOL;'
```

It prints a single line of 200 hexadecimal characters that starts with `31400501`. Put it in your
environment:

```dotenv
PAYUM_CYPHER_KEY=3140050161205157e7b7bbfdacb4201eac4788fa...
```

### defuse/php-encryption

Install the library:

```bash
composer require defuse/php-encryption:^2
```

Generate a key with the command the library installs:

```bash
vendor/bin/generate-defuse-key
```

It prints a single line that starts with `def0000`. Put it in your environment:

```dotenv
PAYUM_CYPHER_KEY=def00000c794ad36e544b9557c098620e19be5f96529a227d4b22874ce16c9cab2ae90a45b07f20b86349c6e1d892ed042562f86ebb50fbb8b6394b8797c63b12d232db4
```

## Wrap the gateway config storage

Create the cypher with the key, and pass it with your Doctrine storage to `CryptoStorageDecorator`. With
Halite:

```php
<?php
// config.php

use Acme\Entity\GatewayConfig;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Bridge\Halite\Security\HaliteCypher;
use Payum\Core\Storage\CryptoStorageDecorator;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$gatewayConfigStorage = new CryptoStorageDecorator(
    new DoctrineStorage($entityManager, GatewayConfig::class),
    new HaliteCypher(getenv('PAYUM_CYPHER_KEY'))
);
```

With defuse/php-encryption, use `new DefuseCypher(getenv('PAYUM_CYPHER_KEY'))` from
`Payum\Core\Bridge\Defuse\Security\DefuseCypher` instead. `HaliteCypher` also accepts a
`ParagonIE\Halite\Symmetric\EncryptionKey`, so you can keep the key in a file and load it with
`KeyFactory::loadEncryptionKey('/path/to/payum.key')`.

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

The `config` column now holds a ciphertext for each string option, plus an `encrypted` flag. With Halite it
looks like this:

```json
{"type":"MUIFAf5ecLDl...","apiKey":"MUIFAJq3Rk0c...","sandbox":true,"encrypted":true}
```

Halite ciphertexts start with `MUIF`, defuse/php-encryption ciphertexts with `def502`.

The gateway name stays readable, so you can still find a config by name.

## Register the stored gateways

Pass the decorated storage to `setGatewayConfigStorage()`, as
[Store gateway configs in a database](configure-gateway-in-backend.md#register-the-stored-gateways) shows.
The decorator decrypts each config as Payum loads it, so the gateway receives the plain options. Payum
ignores the `encrypted` flag the decorator adds. An admin form that loads a config through the decorated
storage also shows the plain values.

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

If you load a config with a different key or library from the one that encrypted it, the decorator throws.
Halite throws `ParagonIE\Halite\Alerts\InvalidMessage`, defuse/php-encryption throws
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

## Change the key or switch to another library

To replace the key, or to move from defuse/php-encryption to Halite, load every config with a decorator that
uses the old cypher and save it with one that uses the new cypher. This script moves configs from
defuse/php-encryption to Halite:

```php
<?php
// reencrypt_configs.php

use Acme\Entity\GatewayConfig;
use Payum\Core\Bridge\Defuse\Security\DefuseCypher;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\Bridge\Halite\Security\HaliteCypher;
use Payum\Core\Storage\CryptoStorageDecorator;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$oldStorage = new CryptoStorageDecorator(
    new DoctrineStorage($entityManager, GatewayConfig::class),
    new DefuseCypher(getenv('PAYUM_CYPHER_KEY_OLD'))
);
$newStorage = new CryptoStorageDecorator(
    new DoctrineStorage($entityManager, GatewayConfig::class),
    new HaliteCypher(getenv('PAYUM_CYPHER_KEY'))
);

foreach ($oldStorage->findBy([]) as $gatewayConfig) {
    $newStorage->update($gatewayConfig);
}
```

To change only the key, use the same cypher class on both sides. Run the script once. A second run fails,
because the old cypher cannot read the configs the first run re-encrypted.

{% hint style="warning" %}
Deploy the new key and cypher at the same time as you run the script. Requests that still use the old ones
cannot read the re-encrypted configs. Back up the gateway config table before you start.
{% endhint %}

## Advanced: use your own model or cypher

`CryptoStorageDecorator` works with any model that implements `Payum\Core\Security\CryptedInterface`. Its
`encrypt()` and `decrypt()` methods receive a `Payum\Core\Security\CypherInterface` and decide which fields
to change. Implement `CypherInterface` yourself to encrypt with a library Payum does not ship a cypher for,
or with a key management service. Its `encrypt()` and `decrypt()` methods take and return strings.

## Supporting Payum

Payum is an MIT-licensed open source project. Its development is paid for by the community and our
customers. If you would like to join them, [become a sponsor](https://github.com/sponsors/payum).
