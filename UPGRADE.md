0.5 to 0.6
==========

* [Doctrine] `TokenizedDetails` mapping schema was updated. details field is now accept `NULL`.

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