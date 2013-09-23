0.4 to 0.5
==========

* [Doctrine] `PaymentDetails` mapping schema was updated. Two fields added: `returnurl`, `cancelurl`.

0.3 to 0.4
==========

* `PaymentInstruction` was renamed to `PaymentDetails` and moved to `Model` namespace.
* [Doctrine] `PaymentDetails` mapping schema was updated. Two fields added: `l_billingtypennn`, `l_billingagreementdescriptionnnn`

0.2 to 0.3
==========

* Remove `toNvp` and `fromNvp` from `PaymentInstruction` class.
* `Payment` class was removed use `Payum\Payment` instead.

0.1 to 0.2
==========

* `SyncRequest` was moved to core lib.