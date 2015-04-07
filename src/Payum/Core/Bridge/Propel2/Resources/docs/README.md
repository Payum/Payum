Propel2 Bridge for Payum
===

Getting started with Propel2 bridge
---

First, you have to generate the model base classes.

To do that, you have to create a configuration file. 
Please take a look at [propel's documentation](http://propelorm.org/documentation/02-buildtime.html#building-the-model) to write that file.

Then run:
```sh
$ bin/propel --config-dir=path/where/you/created/propel.ext --schema-dir=src/Payum/Core/Bridge/Propel2/Resources/config --output-dir=src/ build
```

Then you can insert ```src/Payum/Core/Bridge/Propel2/Resources/install/payment.sql``` and ```src/Payum/Core/Bridge/Propel2/Resources/install/token.sql```
in your database(s).

You can copy the ```schema.xml``` file into your project resources and customize it.
If you customize your ```schema.xml``` you'll have to generate the table creation sql file.
You only have to run:
```sh
$ bin/propel --config-dir=your/path/to/propel.xml/directory --schema-dir=your/path/to/schema.xml/directory --output-dir=your-application/resources/ sql:build
```

If you want to add your own logic to the model classes, you can extend the following classes:
- ```Payum\Core\Bridge\Propel2\Model\Payment```
- ```Payum\Core\Bridge\Propel2\Model\PaymentQuery```
- ```Payum\Core\Bridge\Propel2\Model\Token```
- ```Payum\Core\Bridge\Propel2\Model\TokenQuery```

If you don't want to, you only have to use them.

Then, you have to configure a connection.

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