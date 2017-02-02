# Payum Laravel Package. Eloquent Storage

Here we show how to store data in database using [Eloquent ORM](http://laravel.com/docs/4.2/eloquent).

## Usage

Create an eloquent model:

```php
<?php
class Payment extends Illuminate\Database\Eloquent\Model
{
    protected $table = 'payments';
}
```

Register a storage for it 

```php
// bootstrap/start.php

use Payum\LaravelPackage\Storage\EloquentStorage;

App::resolving('payum.builder', function(\Payum\Core\PayumBuilder $payumBuilder) {
    $payumBuilder
        ->addStorage(Payment::class, new EloquentStorage(Payment::class))
    ;
});
```

## Models 

The package provides two models `Payum\LaravelPackage\Model\Token` and `Payum\LaravelPackage\Model\Payment` which may be reused directly or extend with some custom logic.
Here's the models schemas:

Payment:

The database schema could be generated like this

```php
<?php

\Schema::create('payum_payments', function($table) {
    /** @var \Illuminate\Database\Schema\Blueprint $table */
    $table->bigIncrements('id');
    $table->text('details');
    $table->string('number');
    $table->string('description');
    $table->string('clientId');
    $table->string('clientEmail');
    $table->string('totalAmount');
    $table->string('currencyCode');
    $table->timestamps();
});
```

The storage could be registered like this

```php
// bootstrap/start.php

use Payum\LaravelPackage\Storage\EloquentStorage;
use Payum\LaravelPackage\Model\Payment;

App::resolving('payum.builder', function(\Payum\Core\PayumBuilder $payumBuilder) {
    $payumBuilder
        ->addStorage(Payment::class, new EloquentStorage(Payment::class))
    ;
});
```


Token:

The database schema could be generated like this

```php
<?php

\Schema::create('payum_tokens', function($table) {
    /** @var \Illuminate\Database\Schema\Blueprint $table */
    $table->string('hash')->primary();
    $table->text('details');
    $table->string('targetUrl');
    $table->string('afterUrl');
    $table->string('gatewayName');
    $table->timestamps();
});
```

The token storage could be registered like this

```php
// bootstrap/start.php

use Payum\LaravelPackage\Storage\EloquentStorage;
use Payum\LaravelPackage\Model\Token;

App::resolving('payum.builder', function(\Payum\Core\PayumBuilder $payumBuilder) {
    $payumBuilder
        ->setTokenStorage(new EloquentStorage(Token::class))
    ;
});
```

Back to [index](../index.md).
