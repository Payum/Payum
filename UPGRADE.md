# Upgrades

## 1.3.0

* [http-client] When you update to Payum 1.3.0 the installation will fail because you need to install a client implementation. If you choose php-http/guzzle6-adapter everything will work exactly as before.
* Api constructor's arguments are not optional any more.
* [gateway-factory] Option 'guzzle.client' was removed.
* [security] A new method `createPayoutToken` was added to `GenericTokenFactoryInterface` interface.
* [request] New methods `isPayedout` and `markPayedout` were added to `GetStatusInterface` request.

## 1.2.0

* [stripe] Stripe api version updated to 2.0 - 3.x. You'll have to update any custom actions that use the Stripe api directly.

## 1.0

* Php required version is 5.5
* [be2bill] `Api::getOnsiteUrl` method is renamed to `Api::getOffsiteUrl`
* [be2bill] `Api` methods are returing array instead of Response
* [http-client] kriswallsmith/buzz http client was replaced with PSR7 compatible guzzlehttp/guzzle.

## 0.15 to never

Libraries and extension dependencies are not required now. You must install them if you need.
This make sense for:
* "Authorizenet.NET"
* "PayPal REST API"
* "Klarna Checkout"
* "Klarna Invoice"
* "Stripe"
* "Payex"

## 0.14 to 0.15

* [order] The method getCreditCard was added to `OrderInterface` interface.
* [klarna-checkout] `CreateOrderAction` and `UpdateOrderAction` do not do fetch any more.
* `Payment` class deprecated and will be removed in 0.15. Use `Gateway`.
* `PaymentInterface` interface deprecated and will be removed in 0.15. Use `GatewayInterface`.
* `PaymentFactoryInterface` interface deprecated and will be removed in 0.15. Use `GatewayFactoryInterface`.
* `PaymentFactory` class deprecated and will be removed in 0.15. Use `GatewayFactory`.
* `PaymentAwareInterface` interface deprecated and will be removed in 0.15. Use `GatewayAwareInterface`.
* `PaymentFactoryRegistryInterface` interface deprecated and will be removed in 0.15. Use `GatewayFactoryRegistryInterface`.
* Use `GatewayFactoryRegistryInterface::getGatewayFactory` method instead of `PaymentRegistryInterface::getPaymentFactory`.
* Use `GatewayFactoryRegistryInterface::getGatewayFactories` method instead of `PaymentRegistryInterface::getPaymentFactories`.
* `PaymentRegistryInterface` interface deprecated and will be removed in 0.15. Use `GatewayRegistryInterface`.
* Use `GatewayRegistryInterface::getGateway` method instead of `PaymentRegistryInterface::getPayment`.
* Use `GatewayRegistryInterface::getGateways` method instead of `PaymentRegistryInterface::getPayments`.
* `PaymentConfigInterface` interface deprecated and will be removed in 0.15. Use `GatewayConfigInterface`.
* `PaymentConfig` class deprecated and will be removed in 0.15. Use `GatewayConfig`.
* `PaymentConfig::getPaymentName` and related property renamed to `GatewayConfig::getGatewayName`. 
* `Order` class deprecated and will be removed in 0.15. Use `Payment`.
* `OrderInterface` interface deprecated and will be removed in 0.15. Use `PaymentInterface`.
* `Payum\AuthorizeNet\Aim\PaymentFactory` renamed to `AuthorizeNetAimGatewayFactory`.
* `Payum\Core\Bridge\Symfony\Form\Type\PaymentConfigType` renamed to `GatewayConfigType`.
* `Payum\Core\Bridge\Symfony\Form\Type\PaymentFactoriesChoiceType` renamed to `GatewayFactoriesChoiceType`.
* [doctrine] PaymentConfig::paymentName property renamed to `gatewayName`. **You have to migrate your database**.
* [doctrine] Token::paymentName property renamed to `gatewayName`. **You have to migrate your database**.
* [doctrine] Order renamed to `Payment`. Database schema was changed. **You have to migrate your database**.
* [doctrine] The `currencyDigitsAfterDecimalPoint` property removed from ORM\ODM schema.
* [propel] PaymentConfig::paymentName property renamed to `gatewayName`. **You have to migrate your database**.
* [propel] Token::paymentName property renamed to `gatewayName`. **You have to migrate your database**.
* [propel] Order renamed to `Payment`. Database schema was changed. **You have to migrate your database**.
* [propel] The `currencyDigitsAfterDecimalPoint` property removed from schema.
* [be2bill] Method `Api::prepareOnsitePayment` was renamed to `Api::prepareOffsitePayment`.
* [action] Action `CaptureOrderAction` was renamed to `CapturePaymentAction`.
* [action] Actions `FillOrderDetailsAction` removed. Use `ConvertPaymentAction` ones instead.
* [request] Request `FillOrderDetails` removed. Use `Convert` one instead.
* [model] The method `PaymentInterface::getCurrencyDigitsAfterDecimalPoint` was removed. Use `GetCurrency::getIso4217` request method to get same info.
* [storage] `StorageInterface::findBy` returned value is changed. It was a model or null now it is always an array.
* [extension] The method `ExtensionInterface::onReply` was removed. Use `ExtensionInterface::onPostExecute` and check whether context contains reply or not.
* [extension] The method `ExtensionInterface::onException` was removed. Use `ExtensionInterface::onPostExecute` and check whether context contains exception or not.
* [extension] The method `ExtensionInterface::onPreExecute` signature was changed. Now it takes instance of `Context`.
* [extension] The method `ExtensionInterface::onExecute` signature was changed. Now it takes instance of `Context`.
* [extension] The method `ExtensionInterface::onPostExecute` signature was changed. Now it takes instance of `Context`.

## 0.13 to 0.14

* [registry] `PaymentRegistryInterface::getDefaultPaymentName` method was removed.
* [registry] `PaymentRegistryInterface::getPayment` the argument `name` is always required.
* [registry] `AbstractRegistry::__construct` third `$defaultPayment` argument was removed.
* [storage] A new method `StorageInterface::findBy` was added.

## 0.12 to 0.13

* [request] `markSuspended` method added to `GetStatusInterface` interface. 
* [omnipay-bridge] Omnipay bridge is not shipped with `payum\payum` code any more. Install it separatly by requiring `payum/omnipay-bridge` package.
* [paypal-pro] Remove `PaymentDetails` class.
* [paypal-pro] Remove `Request` and `Response` classes. Use ones from Buzz.
* [paypal-pro] Remove `trxtype` option from api.
* [paypal-pro] Rename `Api::doPayment` to `Api::doSale`. Change the first argument now it is array and the method returns array too.
* [be2bill] `Api` constructor arguments order was changed. Second argument `options` is now first, and the client now is second and optional.
* [paypal-ipn] `Api` constructor arguments order was changed. Second argument `options` is now first, and the client now is second and optional.
* [paypal-pro] `Api` constructor arguments order was changed. Second argument `options` is now first, and the client now is second and optional.
* [payment] Method `PaymentInterface::addApi` was removed from interface, still available in `Payment` class.
* [payment] Method `PaymentInterface::addAction` was removed from interface, still available in `Payment` class.
* [payment] Method `PaymentInterface::addExtension` was removed from interface, still available in `Payment` class.
* [storage] Method `StorageInterface::createModel` was renamed to `create`.
* [storage] Method `StorageInterface::supportModel` was renamed to `support`.
* [storage] Method `StorageInterface::updateModel` was renamed to `update`.
* [storage] Method `StorageInterface::deleteModel` was renamed to `delete`.
* [storage] Method `StorageInterface::findModelById` was renamed to `find`.
* [storage] Method `StorageInterface::getIdentificator` was renamed to `identify`.
* [storage] Method `StorageInterface::findByIdentificator` was removed. Use `find` method instead.
* [storage] Class `Identificator` was deprecated. Use `Identity` instead.
* [factory] Payment factories were changed significantly. Now they implements `PaymentFactoryInterface` and therefor have to accept only array of options as first argument.
* [be2bill] Action `CaptureOnsiteAction` was renamed to `CaptureOffsiteAction`.
* [be2bill] Factory `OnsitePaymentFactory` was renamed to `OffsitePaymentFactory`.
* [be2bill] Factory `PaymentFactory` was renamed to `DirectPaymentFactory`.
* [stripe] Factory `PaymentFactory` was splitted into two: `JsPaymentFactory` and `CheckoutPaymentFactory`.


## 0.11 to 0.12

* [refund] `createRefundToken` was added to `AbstractGenericTokenFactory`.
* [request] `BaseModelAware` request was renamed to `Generic`.
* [request] `SecuredInterface` allows to return a null on `getToken` method call.
* [request] `SecuredAuthorize` request was removed. The removed logic is now in `Authorize` request, so use it.
* [request] `SecuredNotify` request was removed. The removed logic is now in `Notify` request, so use it.
* [request] `SecuredCapture` request was removed. The removed logic is now in `Capture` request, so use it.
* [request] `Notify` request does not contains `notification` array any more. You have to execute `$this->payment->execute($httpRequest = new GetHttpRequest);`.
* [request] `GetHumanStatus::STATUS_SUCCESS` was removed. Use `STATUS_CAPTURED` from the same class.
* [request] `GetHumanStatus::markSuccess` method was removed. Use `markCaptured` from the same class.
* [request] `GetHumanStatus::isSuccess` method was removed. Use `isCaptured` from the same class.
* [request] `GetBinaryStatus::STATUS_SUCCESS` was removed. Use `STATUS_CAPTURED` from the same class.
* [request] `GetBinaryStatus::markSuccess` method was removed. Use `markCaptured` from the same class.
* [request] `GetBinaryStatus::isSuccess` method was removed. Use `isCaptured` from the same class.
* [request] `SecuredInterface` was moved to `Security` namespace and renamed to `TokenAggregateInterface`.
* [request] `ModelAwareInterface` was moved to `Model` namespace and split into two: `ModelAwareInterface` and `ModelAggregateInterface`.
* [bridge][symfony] Minimum required version is `2.3`.
* [action] `ExecuteSameRequestWithModelDetailsAction` does sub execute even for empty details.
* [offline] `Payum\Offline\Constains::STATUS_SUCCESS` was removed. Use `STATUS_CAPTURED` from the same class.
* [klarna-checkout] `GlobalStateSafeConnector` was removed use `Config` instead.
* [klarna-checkout] `CaptureAction` was renamed to `AuthorizeAction` as it is what it really means.
* [klarna-checkout] The previous `success` status now means `authorized`. 

## 0.11.0 to 0.11.7

* [payment] The injection of apis and payment to an action was moved to execute method. So there maybe a slight BC break if you depend on invalid exceptions thrown when api or payment not set correctly.

## 0.10 to 0.11

* [request] Postfix `Request` was removed. For example `CaptureRequest` become `Capture`.
* [request] `SimpleStatusRequest` was renamed to `GetHumanStatus`.
* [request] `BinaryMaskStatusRequest` was renamed to `GetBinaryStatus`.
* [request] All interactive request were replaced by reply concept. Moved to Reply namespace and renamed. For example `Request\Http\RedirectUrlInteractiveRequest` become `Reply\HttpRedirect`.
* [extension] The method `onInteractiveRequest` was renamed to `onReply`. The first parameter has to be an instance of `ReplyInterface`.
* [security] Method `createAuthorizeToken` method was added to `GenericTokenInterface`.
* [security] New argument `authorizePath` was added to `AbstractGenericTokenFactory` and `GenericTokenFactory`.
* [security][symfony] New argument `authorizePath` was added to `TokenFactory` from Symfony's bridge.

## 0.9 to 0.10

* [request] Class `GetHttpQueryRequest` was removed use `GetRequestRequest` instead.
* [request] Class `PostRedirectUrlInteractiveRequest` was moved to sub namespace `Http`.
* [request] Class `RedirectUrlInteractiveRequest` was moved to sub namespace `Http`.
* [request] Class `ResponseInteractiveRequest` was moved to sub namespace `Http`.
* [doctrine][orm] The column name `array` was renamed to `details` in mapping xml for `ArrayObject` class. You have to take care of the migration in your app code.
* [doctrine][mongodb] The field name `array` was renamed to `details` in mapping xml for `ArrayObject` class. You have to take care of the migration in your app code.
* [paypal ec] `Api` constructor arguments order was changed. Second argument `options` is now first, and the client now is second and optional.
* [paypal ec] `Api` methods which used FormRequest as argument now accept an array.
* [paypal ec] `Api` methods which returned Response now return an array.
* [paypal ec] Class `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Buzz\Response` was removed.
* [paypal ec] Class `Payum\Paypal\ExpressCheckout\Nvp\Exception\HttpResponseAckNotSuccessException` was removed.

## 0.8 to 0.9

* [model] Method `CreditCardInterface::getCardHolder` renamed to `getHolder`
* [model] Method `CreditCardInterface::setCardHolder` renamed to `setHolder`
* [model] `CreditCardInterface` getters does not return `SensitiveValue` anymore. It is used only internally.
* [model] Methods `getExpiryMonth`, `setExpiryMonth`, `getExpiryYear`, `setExpiryYear` removed. Use `setExpiredAt` and `getExpiredAt` instead.
* [be2bill] `PaymentFactory` does not provide support of onsite payments. Use `OnsitePaymentFactory` instead.
* [omnipay] Switch to Omnipay v2.x.
* [omnipay] Rename `CaptureAction` to `OnsiteCaptureAction`. It would not support credit card payments. Use new `CaptureAction` for such payments.
* [request] `UserInputRequiredInteractiveRequest` request was removed.
* [registry] `StorageRegistryInterface::getDefaultStorageName` method was removed.
* [registry] `StorageRegistryInterface::getStorageForClass` renamed to `getStorage`. Do not take second argument any more.
* [registry] `StorageRegistryInterface::getStorages` does not take any arguments any more.

## 0.8.5 to 0.8.6

* [Security] Second argument of `GenericTokenFactory::createNotifyToken` is optional now.

## 0.7 to 0.8

* [Registry] `Registry::registerStorageExtensions` method was removed. the logic of the method is done internally.

## 0.6 to 0.7

* [Composer] All repositories were merged to `payum\payum` one. If you need only core change it to `payum\core`
* All classes they were previously in `Payum` namespace moved to `Payum\Core` one.
* `PaymentRegistryInterface::getPayments` method is added.
* `PaymentInterface::addApi` method signature was changed. Now it takes second argument `forcePrepend`.
* [Be2Bill][Doctrine] `Payum\Be2Bill\Bridge\Doctrine\Entity\PaymentDetails` is removed.
* [Be2Bill][Model] `Payum\Be2Bill\Model\PaymentDetails` is removed.
* [Payex][Doctrine] `Payum\Payex\Bridge\Doctrine\Entity\PaymentDetails` is removed.
* [Payex][Doctrine] `Payum\Payex\Bridge\Doctrine\Entity\AgreementDetails` is removed.
* [Payex][Model] `Payum\Payex\Model\PaymentDetails` is removed.
* [Payex][Model] `Payum\Payex\Model\AgreementDetails` is removed.
* [AuthorizeNet][Model] `Payum\AuthorizeNet\Aim\Model\PaymentDetails` is removed.
* [Paypal ExpressCheckout][Model] `BaseModel` was removed.
* [Paypal ExpressCheckout][Model] `Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails` was removed.
* [Paypal ExpressCheckout][Model] `Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails` was removed.
* [Paypal ExpressCheckout][Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\PaymentDetails` was removed.
* [Paypal ExpressCheckout][Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\RecurringPaymentDetails` was removed.
* [Paypal ExpressCheckout][Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\PaymentDetails` was removed.
* [Paypal ExpressCheckout][Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\RecurringPaymentDetails` was removed.

## 0.6.2 to 0.6.3

* [Storage] `AbstractStorage::findModelByIdentificator` does more strict model class comparison now. Only same classes are allowed. Subclasses not allowed any more.

## 0.5 to 0.6

* [Doctrine] `TokenizedDetails` mapping schema was updated. details field is now accept `NULL`.
* [Doctrine] `TokenizedDetails` entity was deprecated use `Token` instead.
* [Doctrine] `TokenizedDetails` mapping changed. The field `token` was renamed to `hash`.
* [Model] `TokenizedDetails::token` property was removed use `hash` one instead.
* [Security] `Random` class was moved to `Payum\Security` namespace.
* [Security] `TokenizedDetails` model was deprecated use `Token` instead.
* [Security] The default token generated in `TokenizedDetails::__constructor` not include `time()` any more.
* [Storage] The method `Storage::supportModel` accepts only model object. Support of model class was removed.
* [Storage] The method `Storage::findModelByIdentificator` was added to storage interface.
* [Request] `CaptureTokenizedDetailsRequest` was deprecated use `SecuredCaptureRequest` instead.
* [Request] `NotifyTokenizedDetailsRequest` was deprecated use `SecuredNotifyRequest` instead.

## 0.4 to 0.5

* A method `getIdentificator` was added to `StorageInterface` interface.
* `StorageExtension` not using scalar as model id any more. Use `Identificator` object instead.
* [Paypal ExpressCheckout][Doctrine] `PaymentDetails` mapping schema was updated. Two fields added: `returnurl`, `cancelurl`.

## 0.3 to 0.4

* Method `StatusInterface::markInProgress` renamed to `StatusInterface::markPending`
* Method `StatusInterface::isInProgress` renamed to `StatusInterface::isPending`
* `StatusInterface` introduce two new statuses: `expired` and `suspended`.
* `BinaryMaskStatusRequest::STATUS_IN_PROGRESS` renamed to `BinaryMaskStatusRequest::STATUS_PENDING`
* `PaymentInstructionAggregateInterface` class renamed to `DetailsAggregateInterface`
* `DetailsAggregateInterface` class moved to `Payum\Model` namespace
* `DetailsAggregateInterface::getPaymentInstruction` renamed to `DetailsAggregateInterface::getDetails`
* `PaymentInstructionAwareInterface` class renamed to `DetailsAwareInterface`
* `DetailsAwareInterface` class moved to `Payum\Model` namespace
* `DetailsAwareInterface::setPaymentInstruction` renamed to `DetailsAwareInterface::setDetails`
* `ActionApiAwareInterface` interface was deleted. Use combination of `ActionInterface` and `ApiAwareInterface` instead.
* `ActionPaymentAwareInterface` interface was deleted. Use combination og `ActionInterface` and `PaymentAwareInterface` instead.
* Action `ActionPaymentAware` was renamed to `PaymentAwareAction`.
* Exception `HttpResponseStatusNotSuccessfulException` was removed. Use `HttpException` instead.
* `HttpException` constructor signature changed. Now it is like any other basic exception.
* [Authorize.Net AIM] `PaymentInstruction` was renamed to `PaymentDetails` and moved to `Model` namespace.
* [Be2Bill] `PaymentInstruction` model was renamed to `PaymentDetails` and moved to `Model` namespace.
* [Be2Bill][Doctrine]`PaymentInstruction` entity was renamed to `PaymentDetails`.
* [Paypal ExpressCheckout] `PaymentInstruction` was renamed to `PaymentDetails` and moved to `Model` namespace.
* [Paypal ExpressCheckout][Doctrine] `PaymentDetails` mapping schema was updated. Two fields added: `l_billingtypennn`, `l_billingagreementdescriptionnnn`
* [Paypal ProCheckout] `PaymentInstruction` model was renamed to `PaymentDetails` and moved to `Model` namespace.

## 0.2 to 0.3

* The `Payment::execute` method signature changed. Now you have to explicitly set when to catch interactive requests or not.
* `InteractiveRequest` renamed to `BaseInteractiveRequest`.
* A method `supportModel` was added to `StorageInterface`.
* `NullStorage` was removed.
* [Authorize.Net AIM] Remove `fillRequest` method from `PaymentInstruction`.
* [Authorize.Net AIM] Remove `updateFromResponse` method from `PaymentInstruction`.
* [Authorize.Net AIM] `Payment` class was removed use `Payum\Payment` instead.
* [Be2Bill] Remove `toParams` and `fromParams` from `PaymentInstruction` class.
* [Be2Bill] `Payment` class was removed use `Payum\Payment` instead.
* [Paypal ExpressCheckout] Remove `toNvp` and `fromNvp` from `PaymentInstruction` class.
* [Paypal ExpressCheckout] `Payment` class was removed use `Payum\Payment` instead.

## 0.1 to 0.2

* `ModelInterface` interface was removed.
* `PaymentInstructionInterface` interface was removed.
* `CreatePaymentInstructionRequest` class was removed.
* `InstructionAwareInterface` moved to `Payum` namespace.
* `InstructionAwareInterface` renamed to `PaymentInstructionAwareInterface`
* `InstructionAwareInterface::setInstruction` renamed to `PaymentInstructionAwareInterface::setPaymentInstruction`
* `InstructionAggregateInterface` moved to `Payum` namespace.
* `InstructionAggregateInterface` renamed to `PaymentInstructionAggregateInterface`
* `InstructionAggregateInterface::getInstruction` renamed to `PaymentInstructionAggregateInterface::getPaymentInstruction`
* `SimpleSell` class was removed.
* Remove Model prefix from `Storages`.
* Change `Storages` namespace. It was `Payum\Domain\Storage\XXX` now `Payum\Storage\XXX`.
* [Authorize.Net AIM] `CaptureAction` now supports only `CaptureRequest` with the `PaymentInstruction` in it.
* [Authorize.Net AIM] `StatusAction` now supports only `StatusAction` with the `PaymentInstruction` in it.
* [Be2Bill] `CaptureAction` now supports only `CaptureRequest` with the `PaymentInstruction` in it.
* [Be2Bill] `StatusAction` now supports only `StatusAction` with the `PaymentInstruction` in it.
* [Paypal ExpressCheckout] `SyncRequest` was moved to core lib.
