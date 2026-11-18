# Encrypt gateway configs stored in database

When you [configure gateways in the backend](configure-payment-in-backend.md), the credentials for your
payment service provider (PSP) are saved in the database as plain text. Give PayumBundle an encryption key
and it encrypts every option of a stored gateway config when it saves it, and decrypts it when it loads it.

## Choose an encryption library

PayumBundle encrypts with one of two libraries:

* [paragonie/halite](https://github.com/paragonie/halite), for new projects. It needs payum/core 2.0 or
  newer.
* [defuse/php-encryption](https://github.com/defuse/php-encryption), for projects that already encrypt with
  it. The library only receives maintenance fixes.

The two cannot read each other's ciphertext. To move from one to the other, see
[Change the key or switch to another library](#change-the-key-or-switch-to-another-library).
[Encrypt stored gateway configs](../encrypt-gateway-configs-stored-in-database.md#choose-an-encryption-library)
compares them in more detail.

## Install the library and generate a key

For Halite, install the library and print a new key:

```bash
composer require paragonie/halite:^5.1
php -r 'require "vendor/autoload.php"; echo ParagonIE\Halite\KeyFactory::export(ParagonIE\Halite\KeyFactory::generateEncryptionKey())->getString(), PHP_EOL;'
```

For defuse/php-encryption, install the library and use the command it ships:

```bash
composer require defuse/php-encryption:^2
vendor/bin/generate-defuse-key
```

Either command prints the key on one line. Store it in an environment variable, for example in
`.env.local` or in your deployment's secrets. With Symfony's
[secrets vault](https://symfony.com/doc/current/configuration/secrets.html) it stays encrypted in the
repository too:

```bash
php bin/console secrets:set PAYUM_CYPHER_KEY
```

{% hint style="warning" %}
Keep a backup of the key somewhere other than the database. If you lose it, nobody can decrypt the stored
configs, and you have to enter every gateway's credentials again.
{% endhint %}

## Configure the bundle

Set the key under `dynamic_gateways.encryption`. With Halite:

```yaml
# config/packages/payum.yaml
payum:
    dynamic_gateways:
        config_storage:
            Acme\PaymentBundle\Entity\GatewayConfig: { doctrine: orm }
        encryption:
            halite_key: '%env(PAYUM_CYPHER_KEY)%'
```

With defuse/php-encryption, use `defuse_secret_key` instead of `halite_key`. Set one of the two, not both.

The bundle wraps the `payum.dynamic_gateways.config_storage` service with
`Payum\Core\Storage\CryptoStorageDecorator`. Payum loads stored gateways through that service, so they
receive the decrypted options without any other change.

| Option | Default | What it does |
| --- | --- | --- |
| `halite_key` | none | Encrypts with Halite. Takes the hex key `KeyFactory::export()` prints. |
| `defuse_secret_key` | none | Encrypts with defuse/php-encryption. Takes the key `generate-defuse-key` prints. |
| `previous.halite_key` or `previous.defuse_secret_key` | none | The key the stored configs were encrypted with before. Only the re-encrypt command reads it. |

## Encrypt configs you have already stored

Configs saved before you set the key keep working, but stay in plain text until they are saved again. Run
the re-encrypt command once to encrypt them all:

```bash
php bin/console payum:security:reencrypt-gateway-configs
```

It prints how many configs it saved. Running it again is safe, because it reads configs that are already
encrypted with the configured key too.

## Change the key or switch to another library

To replace the key, or to move from defuse/php-encryption to Halite, put the new key in place and keep the
old one under `previous`:

```yaml
# config/packages/payum.yaml
payum:
    dynamic_gateways:
        encryption:
            halite_key: '%env(PAYUM_CYPHER_KEY)%'
            previous:
                defuse_secret_key: '%env(PAYUM_CYPHER_KEY_OLD)%'
```

Deploy this config, then run the re-encrypt command:

```bash
php bin/console payum:security:reencrypt-gateway-configs
```

The command reads each config with the new key, or with the previous key if the new one cannot read it, and
saves it encrypted with the new key. If it stops part-way, run it again. A config that neither key can read
stops the command with an error naming the gateway. Once the command succeeds, remove `previous` and the old
key.

{% hint style="warning" %}
Between the deploy and the end of the command, Payum cannot load the gateways whose configs still use the
old key. Run the command as part of the deploy, before traffic reaches the new release, and back up the
gateway config table first.
{% endhint %}

## Read and edit configs through the decorated storage

Only the decorated storage decrypts. Inject `payum.dynamic_gateways.config_storage` wherever your code reads
or saves gateway configs, such as an admin controller. If you load a config with a Doctrine repository
instead, `getConfig()` returns an empty array, and if you save one through the entity manager its new values
are not encrypted.

A form bound to an entity that Doctrine loaded is the common case. Decrypt the config before the form reads
it and encrypt it again after submission, with a form type extension:

```php
<?php

declare(strict_types=1);

namespace Acme\PaymentBundle\Form\Extension;

use Acme\PaymentBundle\Form\Type\PaypalGatewayConfigType;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class CryptedGatewayConfigTypeExtension extends AbstractTypeExtension
{
    public function __construct(private readonly CypherInterface $cypher)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $gatewayConfig = $event->getData();
                if ($gatewayConfig instanceof CryptedInterface) {
                    $gatewayConfig->decrypt($this->cypher);
                }
            })
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
                $gatewayConfig = $event->getData();
                if ($gatewayConfig instanceof CryptedInterface) {
                    $gatewayConfig->encrypt($this->cypher);
                }
            });
    }

    public static function getExtendedTypes(): iterable
    {
        return [PaypalGatewayConfigType::class];
    }
}
```

Pass it the cypher the bundle registers:

```yaml
# config/services.yaml
services:
    Acme\PaymentBundle\Form\Extension\CryptedGatewayConfigTypeExtension:
        arguments: ['@payum.dynamic_gateways.cypher']
```

List every gateway config form type in `getExtendedTypes()`, so one extension covers all of them.

***

### Supporting Payum

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

* [Become a sponsor](https://github.com/sponsors/payum)
