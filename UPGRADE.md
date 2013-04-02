0.3 to 0.4
==========

* `PaymentInstruction` model was renamed to `PaymentDetails` and moved to `Model` namespace.
* [Doctrine]`PaymentInstruction` entity was renamed to `PaymentDetails`.

0.2 to 0.3
==========

* Remove `toParams` and `fromParams` from `PaymentInstruction` class.
* `Payment` class was removed use `Payum\Payment` instead.

0.1 to 0.2
==========

* `CaptureAction` now supports only `CaptureRequest` with the `PaymentInstruction` in it.
* `StatusAction` now supports only `StatusAction` with the `PaymentInstruction` in it.