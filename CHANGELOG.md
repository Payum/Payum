# Changelog

## 0.14.2 (2015-03-13)

* [klarna-invoice] Fix payment factory. Always runs in live mode. 

## 0.14.1 (2015-03-12)

* [paypal-ec] Add CancelRecurringPaymentsProfileAction. 
* [doc] update list of supported payments

## 0.14.0 (2015-02-20)

* [payment-factory] pass config to core payment factory too.
* [payment-factory] allow set default config to payment factory.
* [payment-factory] add factory name and title to the payment config.
* [payment-factory] return default options when call createConfig.
* [doc] add EloquentStorage to list of support storages.
* [doc] add Propel2Storage to list of supported storages.
* [doc] add Propel1Storage to supported storages.
* [security] add token factory extension.
* [security] use composition of token factories. move some code to plain php bridge.
* [offline] if order details are set don't set it again.
* [storage] Add storage method findBy code improve tests include base models and example schema.
* [registry] Add DynamicRegistry registry.
* [symfony][form] Add payment config form, and payment factories choice.
* [registry] Introduce payment factories registry interface.
* [payex] add missed action to factory.
* [paypal-express-checkout] Paypal request parameters should be lowercased.
* [paypal-pro] fix api in factory.
* [payment-factory] options lower case
* [payment-factory] allow set which actions\apis\extensions must be prepend.

## 0.13.0 (2014-12-26)

* [ssl] Added cURL Options for the PayPal Sandbox SSL 3.0 Vulnerability (POODLE)
* [payment] remove all addXXX methods from PaymentInterface.
* [payment] Introduce `PaymentFactoryInterface`. Simplify exist factories.
* [request] Add missed markSuspended method to GetStatusInterface.
* [request] Allow get raw content of the request.
* [reply] Add ability to set Http StatusCode and headers.
* [action] Remove generic order action. Improve execute same request with details action.
* [action] Execute same request with details has to be the last action.
* [storage] Rename methods. Improve usage of Identity.
* [doctrine] fix mapping of `details` field in the order model. It has to be object.
* [buzz] Create default curl client, if no client is passed
* [security] Reuse league/url while manipulation with tokens urls.
* [security] Making `afterPath` optional on `TokenFactoryInterface`.
* [paypal-pro] Add support of refunds.
* [paypal-pro] Refactor Paypal Pro, remove not used stuff, simplify logic.
* [omnipay] Allow install Omnipay bridge ONLY as a standalone package. Not shipped with payum/payum any more.
* [symfony] Allow configure min max expiration years.
* [docs] Add chapter about refund.

## 0.12.9 (2014-12-24)

* [authorize.net] Amount must be decimal.
* [request] add `getFirstModel` method to `Generic` request.

## 0.12.8 (2014-12-13)

* [action] Back port from master a `GenericOrderAction`. Fixes "Attempted to call method "getClientId" in done script but got array instead of order" 

## 0.12.7 (2014-12-11)

* [klarna-checkout] Mark failed if error_code set in details.

## 0.12.6 (2014-11-20)

* [action] Add `NotifyOrderAction` action.

## 0.12.5 (2014-11-11)

* [doc] add sagepay and redsys to list of supported payments.
* [security] token factory should accept Identificator instance as model.
* [paypal-ec] use capture\authorize details as details for notify token.

## 0.12.4 (2014-11-10)

* [request] add cancel request.
* [stripe] description in template must have default val.

## 0.12.3 (2014-11-08)

* [paypal-ec] Allow purchase order with custom details

## 0.12.2 (2014-11-07)

* [security] Added cURL Options for the PayPal Sandbox SSL 3.0 Vulnerability (POODLE)
* [omnipay-bridge] catch invalid credit card exception.

## 0.12.1 (2014-10-31)

* [symfony-bridge][forms] Fix exception "there is only 'years' option available."
* [symfony-bridge] Add obtain credit card basic template.
* [doc] add Silex to the list of supported frameworks.

## 0.12.0 (2014-10-29)

* [action] add basic get http request action.
* [action] `ExecuteSameRequestWithModelDetailsAction` now do sub request for empty details too.
* [request] move `ModelAwareInterface` to model namespace and `TokenAwareInterface` to Security one.
* [request] add `Refund` request
* [request] remove usages of STATUS_SUCCESS and all related methods.
* [request] `BaseModelAware` request was renamed to `Generic` one.
* [request] move logic from `SecuredXXX` requests to `Generic`, remove all `SecuredXXX` requests.
* [tests] reduce code duplications in tests, introduce `GenericActionTest`.
* [stripe] add `GetHttpRequestAction` to checkout factory.
* [stripe] add `GetHttpRequestAction` to js factory.
* [bridge][symfony] add `ObtainCreditCardAction` and credit card form to bridge from bundle.
* [bridge][symfony] add symfony response to reply converter.
* [orders] unified interface for all possible payments.
* [order][paypal] set automatically notify url if not defined.
* [order][doctrine] add mongo and orm mappings for order model.
* [order][payex] add fill order details action.
* [order][paypal-pro]  add fill order details action.
* [offline] add fill order details action.
* [omnipay-bridge] add fill order details action.
* [omnipay-bridge] refactor tests, add auto fill of returnUrl, cancelUrl and clientIp.
* [omnipay-bridge] fix undefined offset 1 error. set holder name as first name always.
* [klarna-checkout] fix bad request. merchant.id invalid.
* [klarna-checkout] Remove GlobalXXXConnector. Replace CaptureAction with Authorize one.
* [klarna-invoice] add refund support.
* [paypal] correctly recognize authorize status.
* [paypal] add make status action aware of refunded payment status.

## 0.11.8 (2014-10-19)

* [klarna-checkoout] set correctly orderid in notify action.
* [klarna-invoice] populate klarna from details must be inside try catch block.

## 0.11.7 (2014-09-29)

* [payment] Critcal bug fix. It was possible to mix credentials of different payments if you use them in single process.

## 0.11.6 (2014-09-28)

* [paypal][ipn] do not update express checkout related info when session has expired.
* [klarna] add `CreditPart` action.

## 0.11.5 (2014-09-18)

* [klarna] error_code has to be int.

## 0.11.4 (2014-09-17)

* [klarna] fix encoding when setting error code, message back to details.

## 0.11.3 (2014-09-17)

* [storage] fix bug "Interface Doctrine\Common\Persistence\Proxy does not exist".

## 0.11.2 (2014-09-16)

* [paypal-pro] `StatusAction` has to accept `GetStatusInterface`, not only `GetBinaryStatus` instance.

## 0.11.1 (2014-09-15)

* [omnipay] Fix undefined offset 1 error. No more splits, the holder name is set as a first name.
* [doctrine][registry] fix ability to get a storage by passing doctrine proxy object.

## 0.11.0 (2014-09-10)

* [klarna] add klarna invoice support.
* [request] add authorize request.
* [tests] upgrade phpunit up to 4.x
* [request] Rename interactive requests to replies. `RedirectUrlInteractiveRequest` become `HttpRedirect` reply.
* [request] Remove `Request` postfix. CaptureRequest become just Capture.
* [request] Rename `SimpleStatusRequest` to `GetHumanStatus` request.
* [request] Rename `BinaryMaskStatusRequest` to `GetBinaryStatus` request.

## 0.10.0 (2014-07-31)

* [doc] fix typos, improve examples, get-it-started page.
* [paypal ec][api] simplify Api class constructor. The client is optional now.
* [paypal ec][api] Api methods now takes array as argument and return array as well. Do not expose buzz outside Api.
* [tests] remove fragile functional tests.
* [composer] fix typos in composer.json
* [http] add client ip and user agent to the get http request.
* [doctrine] rename `array` property to `details`.
* [stripe] add stripe.js, checkout support. 
* [template] use twig as a templating engine.
* [template] add twig factory, simplify first setup.
* [template] allow configure a layout.
* [request] remove deprecated requests.
* [registry] decouple `StorageRegistry` from payments removing name argument.

## 0.9.3 (2014-07-21)

* [doctrine] Quote a column  `array` in metadata for `ArrayObject` class. Array is a reserved word in some databases.

## 0.9.2 (2014-07-08)

* [request] fix post redirect interactive request, it has to extend response interactive request.

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

