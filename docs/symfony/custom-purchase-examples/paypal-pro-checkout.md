# Paypal pro checkout

Steps:

* [Download libraries](#download-libraries)
* [Configure gateway](#configure-context)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](../get-it-started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require "payum/paypal-pro-checkout-nvp"
```

## Configure gateway

```yaml
#app/config/config.yml

payum:
    gateways:
        your_gateway_here:
            factory: paypal_pro_checkout
            username: 'EDIT ME'
            password: 'EDIT ME'
            partner:  'EDIT ME'
            vendor:   'EDIT ME'
            tender:  C
            sandbox: true
```

_**Attention**: You have to change `your_gateway_name` to something more descriptive and domain related, for example `post_a_job_with_paypal`._

_**Note**: `tender`: `C` for Credit card, `P` for PayPal, `A` for Automated Clearinghouse (ACH). [Read more](https://developer.paypal.com/docs/classic/payflow/recurring-billing/#required-parameters-for-the-modify-and-reactivate-actions)_

## Prepare payment

Now we are ready to prepare the payment. Here we set price, currency, cart items details and so.
Please note that you have to set details in the payment gateway specific format.

```php
<?php
//src/Acme/PaymentBundle/Controller
namespace AcmeDemoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function preparePaypalProCheckoutPaymentAction(Request $request)
    {
        $gatewayName = 'your_gateway_name';

        $storage = $this->get('payum')->getStorage('Acme\PaymentBundle\Entity\PaymentDetails');

        /** @var \Acme\PaymentBundle\Entity\PaymentDetails $details */
        $details = $storage->create();
        $details['amt'] = 1;
        $details['currency'] = 'USD';
        $storage->update($details);

        $captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $details,
            'acme_payment_done' // the route to redirect after capture;
        );

        return $this->redirect($captureToken->getTargetUrl());
    }
}
```

That's it. It will ask user for credit card and convert it to payment specific format. After the payment done you will be redirect to `acme_payment_done` action.
Check [this chapter](../purchase-done-action.md) to find out how this done action could look like.

If you still able to pass credit card details explicitly:

```php
<?php
use Payum\Core\Security\SensitiveValue;

$details['acct'] = new SensitiveValue('5105105105105100');
$details['cvv2'] = new SensitiveValue('123');
$details['expDate'] = new SensitiveValue('1214');
```

## Next Step

* [Examples list](../custom-purchase-examples.md).
* [Back to index](../../index.md).
