# Frameworks and e-commerce integration

Payum is a plain PHP library, so it works in any application. Integration packages for several
frameworks wire it into the framework's container, routing and templates for you. This page lists
them.

## Frameworks

Whichever framework you use, [Integrate with a framework](di/framework-integration.md) explains how
to hand your application's container to Payum and share its services with your gateways.

These packages integrate Payum with a framework. Each is maintained in its own repository, so check
its README for the Payum version it supports:

* [PayumBundle](https://github.com/Payum/PayumBundle) for [Symfony](https://symfony.com/)
* [PayumLaravelPackage](https://github.com/Payum/PayumLaravelPackage) for [Laravel](https://laravel.com/)
* [PayumModule](https://github.com/Payum/PayumModule) for [Laminas](https://getlaminas.org/)
* [PayumYiiExtension](https://github.com/Payum/PayumYiiExtension) for [Yii](https://www.yiiframework.com/)
* [PayumSilexProvider](https://github.com/Payum/PayumSilexProvider) for Silex, which is no longer
  maintained

The [Symfony](symfony/get-it-started.md), [Laravel](laravel/get-it-started.md) and
[Silex](silex/get-it-started.md) pages in these docs have not been updated for Payum 2 yet.

## E-commerce

[Sylius](https://sylius.com/), an e-commerce platform built on Symfony, takes payments through
Payum.
