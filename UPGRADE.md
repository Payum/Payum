0.6.2 to 0.6.3
==============

* [Storage] `AbstractStorage::findModelByIdentificator` does more strict model class comparison. Now only same same classes are allowed.

0.5 to 0.6
==========

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

0.4 to 0.5
==========

* A method `getIdentificator` was added to `StorageInterface` interface.
* `StorageExtension` not using scalar as model id any more. Use `Identificator` object instead.

0.3 to 0.4
==========

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

0.2 to 0.3
==========

* The `Payment::execute` method signature changed. Now you have to explicitly set when to catch interactive requests or not.
* `InteractiveRequest` renamed to `BaseInteractiveRequest`.
* A method `supportModel` was added to `StorageInterface`.
* `NullStorage` was removed.

0.1 to 0.2
==========

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