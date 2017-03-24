# Encrypt gateway configs stored in database

To encrypt (and later decrypt) sensitive configuration details (like payment provider credentials) we have to do two things:
 
* Make sure model implements `CryptedInterface`. The `GatewayConfig` class already does it.
* Create a cypher instance. 
* Wrap the storage into `CryptoStorageDecorator` decorator.

First, we have to install an encryption library `defuse/php-encryption`

```bash
$ composer require defuse/php-encryption:^2
```

Once the library is installed we can configure a storage:

```php
<?php
namespace Acme;

use Payum\Core\Storage\CryptoStorageDecorator;
use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var \Payum\Core\Storage\StorageInterface $realStorage */

// the secret has to be stored somewhere and used for all future usages.
$secret = \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString();
$cypher = new \Payum\Core\Bridge\Defuse\Security\DefuseCypher($secret);

$gatewayConfigStorage = new CryptoStorageDecorator($realStorage, $cypher);

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->setGatewayConfigStorage($gatewayConfigStorage)

    ->getPayum()
;
```

Back to [index](index.md).
