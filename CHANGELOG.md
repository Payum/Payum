# Changelog

## 0.6.2 (2013-10-27)

* [doctrine] add functional tests for doctrine storage and mongo manager
* [storage] move identificator to model namespace. deprecate storage one

## 0.6.1 (2013-10-26)

* [doctrine] deprecate bridge models. provide mappings for basic ones

## 0.6.0 (2013-10-25)

* [mongo] add custom type `ObjectType` to avoid probmels with mongo.
* [request] add base status request.
* [security] allow set any details to token.
* [storage] better handling of id property in filesystem storage.
* [storage][mongo] add mongodb support
* [doc] add managing notifications doc
* [doc] add get it started doc
* [registry] add simple registry
* [storage] schedule for update models set in action
* [storage] do not throw on not supported identifier
* [security] introduce security layer. added `HttpRequestInterface`, `TokenInterface`
* [storage][extension] update model after last request is executed
* [storage][extension] update model on exception as well.
* [storage] add `StorageInterface::findModelByIdentifier` method. update abstract storage
* [storage] add abstract storage
* [storage] mark deprecated `Storage::supportModel`
* [log] add documentation.
* [log] adjust log messages.
* [log] suggest monolog as logger.
* [extension] add debug extension that wrtite call stack to log
* [extension] add PSR-3 log extensions
* [request] `StatusRequestInterface` now extends `ModelRequestInterface`
* [spl] add `ArrayObject::validatedKeysSet` to check array key present or not

## 0.5.4 (2013-07-15)

* [security] make details property optional in `TokenizedDetails` model
* [request] add instant payment notifications request

## 0.5.3 (2013-06-24)

* No changes 

## 0.5.2 (2013-06-24)

* [request] move `CaptureTokenizedDetails` request from the bundle

## 0.5.1 (2013-06-12)

* [spl] add `ArrayObject::validatedKeysSet` to check array key present or not.

## 0.5.0 (2013-06-03)

* add the architecture doc
* add `StorageRegistryInterface::getStorages` method
* add `RegistryInterface` interface.
* [storage] ensure models always is saved
* [storage] add `StorageInterface::deleteModel` method
* [security] add targetUrl, afterUrl to TokenizedDetails model
* [security] introduce `TokenizedDetails` model
* [storage] add identificator model to easy find payment models
* fix suggest section of `composer.json`
* add keywords to `composer.json`
* add `StorageRegistryInterface` interface
* add `PaymentRegistryInterface` interface

## 0.4.0 (2013-04-04)

* remove `HttpResponseStatusNotSuccessException`
* rename `ActionPaymentAware` to `PaymentAwareAction`
* remove `ApiAwareActionInterface`
* remove `PaymentAwareActionInterface`
* rename instruction aware\aggregate models to details ones
* add expired and suspended statuses.
* rename `StatusRequestInterface::isInProgress` to `isPending`
* remove `ArrayObject::offsetsExists` method.
* add `ArrayObject::validateNotEmpty` method

## 0.3.1 (2013-03-28)

* allow any stable buzz version

## 0.3.0 (2013-03-11)

* add `forcePrepend` option to `PaymentInterface::addAction` method
* add `ArrayObject::ensure` method. do not create nested models arrays
* fix variaty bugs in ArrayObject implementation
* add an action for `PaymentInstructionAggregate` models
* remove null storage
* add storage extension
* add extensions
* add omnipay to the list of supported payments
* change doctrine version to stable in composer
* ability to set several APIs to the `Payment` instance
* fix `ArrayObject::replace` method
* add `BaseModelRequest` and `BaseModelInteractiveRequest`
* update list of supported payments
* add `setModel` method to a request
* catch interactive requests to payment and reutrn it

## 0.2.0 (2013-02-08)

* move `SyncRequest` request from paypal express checkout
* add `NullStorage`
* rework storages, change name, namespace
* remove `SimpleSell` request
* rework `PaymentInstructionAggregate\Aware` interfaces
* remove `CreatePaymentInstructionRequest` class
* remove `ModelInterface` and `PaymentInstruction` interfaces
* add testo lib (generates code examples using test cases)
* add travis status to readme
* add travis

## 0.1 (2012-12-07)

* add status request
* add interactive request
* rename aggregate -> aware
* add domain model
* add storages support
* initial classes and interfaces.

