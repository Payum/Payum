Propel2 Bridge for Payum
===

Getting started with Propel2 bridge
---

In order to use the Propel2 bridge, you have to configure a connection.

Here's a snippet adapted from propel [documentation](http://propelorm.org/documentation/02-buildtime.html#runtime-connection-settings):

```php
<?php

use Propel\Runtime\Propel;
use Propel\Runtime\Connection\ConnectionManagerSingle;
$serviceContainer = Propel::getServiceContainer();
$serviceContainer->setAdapterClass('default', 'mysql');
$manager = new ConnectionManagerSingle();
$manager->setConfiguration(array (
  'dsn'      => 'mysql:host=localhost;dbname=my_db_name',
  'user'     => 'my_db_user',
  'password' => 's3cr3t',
));
$serviceContainer->setConnectionManager('default', $manager);
```

How to generate the model ?
---

Go to payum root and launch:

```sh
$ bin/propel --config-dir=src/Payum/Core/Bridge/Propel2/Resources/config --schema-dir=src/Payum/Core/Bridge/Propel2/Resources/config --output-dir=src/ build 
```

How to generate the default.sql file ?
---

Go to payum root and launch:

```sh
$ bin/propel --config-dir=src/Payum/Core/Bridge/Propel2/Resources/config --schema-dir=src/Payum/Core/Bridge/Propel2/Resources/config --output-dir=src/Payum/Core/Bridge/Propel2/Resources/install sql:build
```

You may remove the generated sqldb.map file as it isn't used anymore after.