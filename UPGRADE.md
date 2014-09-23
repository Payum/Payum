# Upgrades

## 0.11 to 0.12

* [request] `BaseModelAware` request was renamed to `Generic`.

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
