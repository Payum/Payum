0.3 to 0.4
==========

* Method `StatusInterface::markInProgress` renamed to `StatusInterface::markPending`
* Method `StatusInterface::isInProgress` renamed to `StatusInterface::isPending`
* `StatusInterface` introduce two new statuses: `expired` and `suspended`.
* `BinaryMaskStatusRequest::STATUS_IN_PROGRESS` renamed to `BinaryMaskStatusRequest::STATUS_PENDING`

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