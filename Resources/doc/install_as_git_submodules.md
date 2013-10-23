# Install as git submodules

## Step 1. Add submodules

``` bash
$ git submodule add git://github.com/Payum/PayumBundle.git vendor/payum/payum-bundle/Payum/PayumBundle
$ git submodule update --init
```

## Step 2: Configure the Autoloader

You have to add the `Payum\PayumBundle` namespace to your autoloader:

``` php
<?php
// app/autoload.php

//uncomment this if you dont have universal class loader instance.
//require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
//$loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();

$loader->registerNamespaces(array(
    // ...
    'Payum\PayumBundle' => __DIR__.'/../vendor/payum/payum-bundle',
));

$loader->register();
```

## Next Step

* [Back to index](index.md).