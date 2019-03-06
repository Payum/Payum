<h2 align="center">Supporting Payum</h2>

Payum is an MIT-licensed open source project with its ongoing development made possible entirely by the support of community and our customers. If you'd like to join them, please consider:

- [Become a sponsor](https://www.patreon.com/makasim)
- [Become our client](http://forma-pro.com/)

---

# Encrypt gateway configs stored in database

To encrypt (and later decrypt) sensitive configuration details (like payment provider credentials) we have to do four things:

* Make sure model implements `CryptedInterface`. The `GatewayConfig` class already does it.
* Generate a cypher key and store it
* Configure our dynamic gateways
* Create a Form Type Extension

First, we have to install an encryption library `defuse/php-encryption`:

```bash
$ composer require defuse/php-encryption:^2
```

## Generate and Store your Cypher Key

Once the library is installed, you should generate a cypher key:

```bash
$ vendor/bin/generate-defuse-key
```

It will output something like this:
```
def00000c794ad36e544b9557c098620e19be5f96529a227d4b22874ce16c9cab2ae90a45b07f20b86349c6e1d892ed042562f86ebb50fbb8b6394b8797c63b12d232db4
```

For storing your cypher key, the best way is to use a environment variable. 
If your are using [Symfony Dotenv Component](https://symfony.com/doc/current/components/dotenv.html), you can store your cypher key like this:

```dotenv
#.env
PAYUM_CYPHER_KEY=def00000c794ad36e544b9557c098620e19be5f96529a227d4b22874ce16c9cab2ae90a45b07f20b86349c6e1d892ed042562f86ebb50fbb8b6394b8797c63b12d232db4
```

## Configure

Then, you should configure your dynamic gateways:

```diff
#app/config/config.yml

payum:
    dynamic_gateways:
        config_storage: 
            Acme\PaymentBundle\Entity\GatewayConfig: { doctrine: orm }
+        encryption:
+              defuse_secret_key: '%env(PAYUM_CYPHER_KEY)%'
```

## Usage

### With Sonata Admin

If you are using [Sonata Admin integration](./configure-payment-in-backend.md#with-sonata-admin), you can stop here because everything is done automatically.

### The manual way

You should tell to Symfony how to encrypt/decrypt your gateway configuration when you use your `PaypalGatewayConfigType` form type (previously done in [Configure gateway in backend](./configure-payment-in-backend.md)).

For that, you have two solutions:
  1. update your `PaypalGatewayConfigType` form type
  2. create a [Form Type Extension](https://symfony.com/doc/current/form/create_form_type_extension.html) that will modify your `PaypalGatewayConfigType` form type

The second solution is better, because if you have a form type for a second gateway (for example [Stripe.js](../index.md#stripe)),
you won't have to duplicate your logic in your `StripeGatewayConfigType` form type.

We will create a `CryptedGatewayConfigTypeExtension` form type extension in the namespace `Acme\PaymentBundle\Form\Extension`.
Be sure to follow [this step](https://symfony.com/doc/current/form/create_form_type_extension.html#registering-your-form-type-extension-as-a-service) to register your form type extension as a service.

```php
<?php

declare(strict_types=1);

namespace Acme\PaymentBundle\Form\Extension;

use Acme\PaymentBundle\Form\Type\Payment\PaypalGatewayConfigType;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CryptedGatewayConfigTypeExtension extends AbstractTypeExtension
{
    private $cypher;

    public function __construct(?CypherInterface $cypher = null)
    {
        $this->cypher = $cypher;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (null === $this->cypher) {
            return;
        }

        $builder
            // Before set form data, we decrypt the gateway config
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $gatewayConfig = $event->getData();

                if (!$gatewayConfig instanceof CryptedInterface) {
                    return;
                }

                $gatewayConfig->decrypt($this->cypher);

                $event->setData($gatewayConfig);
            })
            // After submitting the form, we encrypt the gateway back
            ->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
                $gatewayConfig = $event->getData();

                if (!$gatewayConfig instanceof CryptedInterface) {
                    return;
                }

                $gatewayConfig->encrypt($this->cypher);

                $event->setData($gatewayConfig);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedTypes(): array
    {
        // The extension will be applied on `PaypalGatewayConfigType` form type.
        // Feel free to add another form types if needed.
        return [PaypalGatewayConfigType::class];
    }
}
```

Back to [index](index.md).
