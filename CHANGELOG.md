# Changelog

## 0.9.1 (2014-07-08)

* [be2bill] fix capture with aliased credit card.

## 0.9.0 (2014-06-17)

* [registry] decouple `StorageRegistry` from payments. The `name` argument was removed.
* [security] allow create notify token without model set.
* [creditcard] allow set null as expire at date.
* [creditcard] allow secure credit card sensitive values.
* [creditcard] `CreditCardInterface` getters does not return `SensitiveValue` anymore.
* [creditcard] rename credit card method `setCardholder` and `getCardholder` to just `setHodler` and `getHolder`.
* [creditcard] use `DateTime` to represent expire date.
* [spl] the method `ArrayObject::validatedNotEmpty` was renamed to `validateNotEmpty`.
* [request] remove `UserInputRequeredRequest` request.
* [omnipay] support Omnipay version >=2.
* [omnipay] add `OnsiteCaptureAction`.
* [omnipay] allow obtain credit card in `CaptureAction`.
* [omnipay] fix not imported namespace, onsite capture, prepare for obtain cc.
* [omnipay] add support of POST redirect.
* [paypal-pro] allow obtain credit card.
* [be2bill] allow obtain credit card.
* [authorize.net] allow obtain credit card.
* [paypal] Add DoReferenceTransactionActionAction and DoReferenceTransactionActionRequest
* [security][symfony] Pass bollean true to url generator. Fixes fatal error on an old symfony's versions.

## 0.8.8 (2014-05-29)

* [request] add ObtainCreditCardRequest.
* [model] add credit card model.
* [security] add mask util. allows mask strings with diff options.
* [doc] configure TokenStorage to use hash field as idPropery.

## 0.8.7 (2014-05-05)

* [request] add simple status request.

## 0.8.6 (2014-05-01)

* [security] allow create notify token without model set.

## 0.8.5 (2014-04-16)

* [be2bill] should not use capture onsite action when aliased credit card used.

## 0.8.4 (2014-04-16)

* [be2bill] api endpoint has ".php" at the end. Fixes temporal glitches with api.

## 0.8.3 (2014-04-14)

* [security][symfony] Fix bug: `PHP Fatal error:  Undefined class constant 'ABSOLUTE_URL' in ...`

## 0.8.2 (2014-04-10)

* [paypal] Set return\cancel (if not set) url from secured capture request.
* [paypal] Add NotifyAction to PaymentFactory.
* [paypal][action] Add generic NotifyAction.
* [security] Fix `GenericTokenFactory`. It did not handle afterPath correctly, when it is url.
* [symfony][request] move symfony's response interactive request to bridge.

## 0.8.1 (2014-04-08)

* [security] symfony's TokenFactory now require UrlGeneratorInterface.
* import namespaces.
* [security][symfony] move HttpRequestVerifier from bundle to bridge.
* [security][symfony] move TokenFactory from symfony bundle to bridge.
* [security] TokenFactory accepts urls as targetPath and afterPath.
* [doc] added laravel to supported frameworks list.
* [offline] wrap model by ArrayObject from bridge.
* [offline] Fixing Notice: Undefined index: paid in ...

## 0.8.0 (2014-04-02)

* [paypal][pro] set bigger timeout to prevent test fails
* [klarna] add support of klarna checkout payment.
* [zend][storage] add zend table gateway storage.
* [omnipay] do not wrap omnipay's exceptions in capture action.
* [security] add generic token factory.
* [security] introduce token factory.
* [action] add tests for notify action
* [request] add response interactive request.
* [payex] remove duplicated tests.
* [registry] register storage extensions lazily.

## 0.7.2 (2014-01-18)

* [be2bill][api] add a method to verify params hash.

## 0.7.1 (2014-01-12)

* [registry] `SimpleRegistry::registerStorageExtensions` method is deprecated.

## 0.7.0 (2013-12-26)

* merge all payment gateway into one payum repository and setup subtree split.
* move all current root classes to core namespace.
* [request] add `GetHttpQueryRequest` request
* [request] add `PostRedirectUrlInteractiveRequest` request
* [request] remove deprecated `XXXDetailsAggregatedModelsAction` actions.
* [request] use execute same request with model details action
* [payment] making `findSupport` method more specific on the return value. Now it returns false when not supported action.
* [composer] add missed libs to replace section
* [security] improve token hash generator
* [security] prevent accidental storing of sensitive info
* [security] wrapp sensitive values to prevent its saving somewhere
* [security] use SensitiveValue to safely process card number etc.
* [storage][filesystem] add identity map for filesystem storage.
* [payment] add `forcePrepend` argument to `Payment::addApi` method.
* [registry] add `SimpleRegistry::registerStorageExtensions` helper method.
* [registry] add `PaymentRegistryInterface::getPayments` method.
* [doctrine][orm] add mapping for simple model.
* [doctrine][mongo] add mapping for simple model.
* [model] add simple unified array model.
* [authorize.net] remove payment details custom model. Use ArrayObject from core or your own.
* [be2bill] add support of  be2bill onsite payments
* [be2bill] fix. capture with credit card should support if `CARDCODE` provided.
* [paypal express checkout][model] remove custom model and doctrine mapping for it.
* [paypal] Allow set custom params to authorize token url
* [payex][model] remove custom model and doctrine mapping for it.

## 0.6.5 (2013-11-22)

* [request] CaptureDetailsAggregatedModelAction is deprecated.
* [request] SyncDetailsAggregatedModelAction is deprecated.
* [request] StatusDetailsAggregatedModelAction is deprecated.

## 0.6.4 (2013-11-19)

* [exception] improve message of request not supported exception.
* [registry] improve exception messages for getStorageForClass method.

## 0.6.3 (2013-10-27)

* [composer] lower required php version from 5.3.9 to 5.3.3

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

