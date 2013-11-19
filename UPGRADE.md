# Upgrades

## 0.6 to 0.7

* [Model] `BaseModel` was removed.
* [Model] `Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails` was removed.
* [Model] `Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails` was removed.
* [Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\PaymentDetails` was removed.
* [Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\RecurringPaymentDetails` was removed.
* [Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\PaymentDetails` was removed.
* [Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\RecurringPaymentDetails` was removed.

## 0.4 to 0.5

* [Doctrine] `PaymentDetails` mapping schema was updated. Two fields added: `returnurl`, `cancelurl`.

## 0.3 to 0.4

* `PaymentInstruction` was renamed to `PaymentDetails` and moved to `Model` namespace.
* [Doctrine] `PaymentDetails` mapping schema was updated. Two fields added: `l_billingtypennn`, `l_billingagreementdescriptionnnn`

## 0.2 to 0.3

* Remove `toNvp` and `fromNvp` from `PaymentInstruction` class.
* `Payment` class was removed use `Payum\Payment` instead.

## 0.1 to 0.2

* `SyncRequest` was moved to core lib.