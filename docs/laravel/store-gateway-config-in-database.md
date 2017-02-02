# Payum Laravel Package. Store gateway config in database

Payum allows you store gateway configuration and credentials in your database.
Later you may provide a backoffice so the admin can modify gateway through it.
Payum Laravel Package contains Eloquent GatewayConfig model, which we are going to reuse in this example.

The database schema could be generated like this

```php
<?php

Schema::create('payum_gateway_configs', function($table) {
    /** @var \Illuminate\Database\Schema\Blueprint $table */
    $table->bigIncrements('id');
    $table->text('config');
    $table->string('factoryName');
    $table->string('gatewayName');
    $table->timestamps();
});
```

The gateway config storage should be registered like this:

```php
// bootstrap/start.php

use Payum\LaravelPackage\Storage\EloquentStorage;
use Payum\LaravelPackage\Model\GatewayConfig;

App::resolving('payum.builder', function(\Payum\Core\PayumBuilder $payumBuilder) {
    $payumBuilder
        ->setGatewayConfigStorage(new EloquentStorage(GatewayConfig::class))
    ;
});
```

Back to [index](../index.md).
