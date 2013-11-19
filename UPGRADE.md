# Upgrades

## 0.6 to 0.7

* [Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\PaymentDetails` is deprecated. Use `Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails` model.
* [Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Entity\RecurringPaymentDetails` is deprecated. Use `Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails` model.
* [Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\PaymentDetails` is deprecated. Use `Payum\Paypal\ExpressCheckout\Nvp\Model\PaymentDetails` model.
* [Doctrine] `Payum\Paypal\ExpressCheckout\Nvp\Bridge\Doctrine\Document\RecurringPaymentDetails` is deprecated. Use `Payum\Paypal\ExpressCheckout\Nvp\Model\RecurringPaymentDetails` model.

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