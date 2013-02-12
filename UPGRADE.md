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