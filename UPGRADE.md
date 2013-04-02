0.3 to 0.4
==========

* `PaymentInstruction` was renamed to `PaymentDetails` and moved to `Model` namespace.

0.2 to 0.3
==========

* Remove `fillRequest` method from `PaymentInstruction`.
* Remove `updateFromResponse` method from `PaymentInstruction`.
* `Payment` class was removed use `Payum\Payment` instead.

0.1 to 0.2
==========

* `CaptureAction` now supports only `CaptureRequest` with the `PaymentInstruction` in it.
* `StatusAction` now supports only `StatusAction` with the `PaymentInstruction` in it.