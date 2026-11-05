# Supported gateways

A gateway is Payum's adapter for one payment provider. Each one is a Composer package that you
install next to `payum/core`. This page lists the packages that exist: the ones maintained in the
Payum repository, the Omnipay bridge and gateways written by the community.

Gateways are built on one of two APIs. Gateways written for Payum 2 are registered with
`PayumBuilder::registerGateway()`, as [Get started](get-it-started.md) shows. Older gateways ship a
1.x gateway factory, which Payum 2 still loads but has deprecated. Check a package's README for the
Payum version it supports before you depend on it. To write a gateway for a provider that is not
listed, see [Build a gateway](develop-gateway-with-payum.md).

## Gateways in the Payum repository

These packages are developed in the [Payum repository](https://github.com/Payum/Payum) and published
as read-only copies, one per package:

| Package | Provider | Documentation |
| :--- | :--- | :--- |
| `payum/authorize-net-aim` | Authorize.Net AIM | [Authorize.Net AIM](authorize-net/aim/get-it-started.md) |
| `payum/be2bill` | Be2Bill direct and offsite | [Be2Bill Direct](be2bill/direct.md), [Be2Bill Offsite](be2bill/offsite.md) |
| `payum/klarna-checkout` | Klarna Checkout | [Klarna Checkout](klarna/checkout/get-it-started.md) |
| `payum/klarna-invoice` | Klarna Invoice | [Klarna Invoice](klarna/invoice/get-it-started.md) |
| `payum/offline` | No provider: records a payment without contacting anyone | [Offline](offline/get-it-started.md) |
| `payum/payex` | Payex | [Payex](payex/get-it-started.md) |
| `payum/paypal-express-checkout-nvp` | PayPal Express Checkout | [PayPal Express Checkout](paypal/express-checkout/get-it-started.md) |
| `payum/paypal-pro-checkout-nvp` | PayPal Pro Checkout | [PayPal Pro Checkout](paypal/pro-checkout/get-it-started.md) |
| `payum/paypal-pro-hosted-nvp` | PayPal Pro Hosted | [PayPal Pro Hosted](paypal/pro-hosted/get-it-started.md) |
| `payum/paypal-masspay-nvp` | PayPal Masspay | [PayPal Masspay](paypal/masspay/get-it-started.md) |
| `payum/paypal-rest` | PayPal REST | [PayPal REST](paypal/rest/get-it-started.md) |
| `payum/paypal-ipn` | PayPal instant payment notifications | [PayPal IPN](paypal/ipn/get-it-started.md) |
| `payum/sofort` | Sofort | [Sofort](sofort/get-it-started.md) |
| `payum/stripe` | Stripe Checkout, Stripe.js and direct charges | [Stripe Checkout](stripe/checkout.md), [Stripe.js](stripe/js.md), [Stripe Direct](stripe/direct.md) |

`payum/payum` installs all of them at once, together with `payum/core`.

## Omnipay gateways

[Omnipay](https://omnipay.thephpleague.com/) is another PHP payment library with many gateways. The
[Omnipay bridge](https://github.com/Payum/OmnipayV3Bridge) (`payum/omnipay-v3-bridge`) lets you use
an Omnipay gateway through Payum. The bridge is built on the 1.x gateway factory API. Install the
Omnipay gateway package you need next to it:

* [thephpleague/omnipay-2checkout](https://github.com/thephpleague/omnipay-2checkout)
* [lokielse/omnipay-alipay](https://github.com/lokielse/omnipay-alipay)
* [thephpleague/omnipay-authorizenet](https://github.com/thephpleague/omnipay-authorizenet)
* [thephpleague/omnipay-buckaroo](https://github.com/thephpleague/omnipay-buckaroo)
* [thephpleague/omnipay-cardsave](https://github.com/thephpleague/omnipay-cardsave)
* [thephpleague/omnipay-coinbase](https://github.com/thephpleague/omnipay-coinbase)
* [dioscouri/omnipay-cybersource](https://github.com/dioscouri/omnipay-cybersource)
* [DABSquared/omnipay-cybersource-soap](https://github.com/DABSquared/omnipay-cybersource-soap)
* [coatesap/omnipay-datacash](https://github.com/coatesap/omnipay-datacash)
* [thephpleague/omnipay-dummy](https://github.com/thephpleague/omnipay-dummy)
* [dercoder/omnipay-ecopayz](https://github.com/dercoder/omnipay-ecopayz)
* [thephpleague/omnipay-eway](https://github.com/thephpleague/omnipay-eway)
* [thephpleague/omnipay-firstdata](https://github.com/thephpleague/omnipay-firstdata)
* [thephpleague/omnipay-gocardless](https://github.com/thephpleague/omnipay-gocardless)
* [thephpleague/omnipay-manual](https://github.com/thephpleague/omnipay-manual)
* [thephpleague/omnipay-migs](https://github.com/thephpleague/omnipay-migs)
* [thephpleague/omnipay-mollie](https://github.com/thephpleague/omnipay-mollie)
* [thephpleague/omnipay-multisafepay](https://github.com/thephpleague/omnipay-multisafepay)
* [thephpleague/omnipay-netaxept](https://github.com/thephpleague/omnipay-netaxept)
* [thephpleague/omnipay-netbanx](https://github.com/thephpleague/omnipay-netbanx)
* [alfaproject/omnipay-neteller](https://github.com/alfaproject/omnipay-neteller)
* [mfauveau/omnipay-pacnet](https://github.com/mfauveau/omnipay-pacnet)
* [thephpleague/omnipay-payfast](https://github.com/thephpleague/omnipay-payfast)
* [thephpleague/omnipay-payflow](https://github.com/thephpleague/omnipay-payflow)
* [thephpleague/omnipay-paymentexpress](https://github.com/thephpleague/omnipay-paymentexpress)
* [coatesap/omnipay-paymentsense](https://github.com/coatesap/omnipay-paymentsense)
* [thephpleague/omnipay-paypal](https://github.com/thephpleague/omnipay-paypal)
* [efesaid/omnipay-payu](https://github.com/efesaid/omnipay-payu)
* [thephpleague/omnipay-pin](https://github.com/thephpleague/omnipay-pin)
* [coatesap/omnipay-realex](https://github.com/coatesap/omnipay-realex)
* [thephpleague/omnipay-sagepay](https://github.com/thephpleague/omnipay-sagepay)
* [thephpleague/omnipay-securepay](https://github.com/thephpleague/omnipay-securepay)
* [justinbusschau/omnipay-secpay](https://github.com/justinbusschau/omnipay-secpay)
* [fruitcakestudio/omnipay-sisow](https://github.com/fruitcakestudio/omnipay-sisow)
* [alfaproject/omnipay-skrill](https://github.com/alfaproject/omnipay-skrill)
* [thephpleague/omnipay-stripe](https://github.com/thephpleague/omnipay-stripe)
* [thephpleague/omnipay-targetpay](https://github.com/thephpleague/omnipay-targetpay)
* [thephpleague/omnipay-worldpay](https://github.com/thephpleague/omnipay-worldpay)
* [aTastyCookie/yandexmoney\_omnipay](https://github.com/aTastyCookie/yandexmoney\_omnipay)

## Community gateways

Other developers maintain these gateways in their own repositories. Payum does not test them, and
some have not been updated for a long time:

* [PayU India and PayUBiz (knit-pay/payum-payu-india)](https://github.com/knit-pay/payum-payu-india)
* [r3s7/PayumDineroMail](https://github.com/r3s7/PayumDineroMail)
* [r3s7/PayumBitPay](https://github.com/r3s7/PayumBitPay)
* [kayue/Paydollar](https://github.com/kayue/Paydollar)
* [locastic/PayumPaylinkJustpay](https://github.com/locastic/PayumPaylinkJustpay)
* [locastic/TcomPayWayPayum](https://github.com/locastic/TcomPayWayPayum)
* [LedjIn/Sagepay](https://github.com/LedjIn/Sagepay)
* [crevillo/payum-redsys](https://github.com/crevillo/payum-redsys)
* [invit/payum-sofortueberweisung (abandoned)](https://github.com/invit/payum-sofortueberweisung)
* [wiseape/payum-sofortuberweisung (abandoned)](https://github.com/wiseape/payum-sofortuberweisung)
* [wiseape/payum-paymill](https://github.com/wiseape/payum-paymill)
* [gdaws/payum\_braintree](https://github.com/gdaws/payum\_braintree)
* [peterfox/Payum-Bitpay](https://github.com/peterfox/Payum-Bitpay)
* [winzou/payum-limonetik](https://github.com/winzou/payum-limonetik)
* [ekipower/payum-nganluong](https://github.com/ekipower/payum-nganluong)
* [tmconsulting/payum-uniteller-gateway](https://github.com/tmconsulting/payum-uniteller-gateway)
* [fullpipe/payum-uniteller](https://github.com/fullpipe/payum-uniteller)
* [fullpipe/payum-flexidengi](https://github.com/fullpipe/payum-flexidengi)
* [paradigm/payum-trustly](https://github.com/Paradigmfi/PayumTrustly)
* [paradigm/payum-paytrail](https://github.com/Paradigmfi/PayumPaytrail)
* [ekyna/PayumSips](https://github.com/ekyna/PayumSips)
* [accesto/PayumPayU](https://github.com/accesto/PayumPayU)
* [pixers/payum-dotpay](https://github.com/pixers/payum-dotpay)
* [khal3d/payum-cashnpay](https://github.com/khal3d/payum-cashnpay)
* [NetTeam/payum-adyen](https://github.com/NetTeam/payum-adyen)
* [NetTeam/payum-paymill](https://github.com/NetTeam/payum-paymill)
* [BoShurik/payum-yandex-money](https://github.com/BoShurik/payum-yandex-money)
* [sergeym/payum-pay-receipt](https://github.com/sergeym/payum-pay-receipt)
* [remyma/payum-paybox](https://github.com/remyma/payum-paybox)
* [artkonekt/payum-otp-hungary](https://github.com/artkonekt/payum-otp-hungary)
* [sourcefabric/payum-mollie](https://github.com/sourcefabric/payum-mollie)
* [sourcefabric/payum-mbe4](https://github.com/sourcefabric/payum-mbe4)
* [valiton/payum-payone](https://github.com/valiton/payum-payone)
* [Setono/payum-quickpay](https://github.com/Setono/payum-quickpay)
* [ilcleme/payum-tinkl](https://github.com/ilCleme/payum-tinkl)
* [mrcmorales/payum-redsys](https://github.com/mrcmorales/payum-redsys)
* [antqa/payum-perfectmoney](https://github.com/antqa/payum-perfectmoney)

To add your gateway to this list, open a pull request against this page in the
[Payum repository](https://github.com/Payum/Payum).
