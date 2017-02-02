# Paypal express checkout

Steps:

* [Download libraries](#download-libraries)
* [Configure gateway](#configure-context)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](../get-it-started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require "payum/paypal-express-checkout-nvp"
```

## Configure gateway

```yaml
#app/config/config.yml

payum:
    gateways:
        your_gateway_here:
            factory: paypal_express_checkout
            username:  'get this from gateway side'
            password:  'get this from gateway side'
            signature: 'get this from gateway side'
            sandbox: true
```

_**Attention**: You have to changed `your_gateway_name` to something more descriptive and domain related, for example `post_a_job_with_paypal`._

## Prepare gateway

Now we are ready to prepare the payment. Here we set price, currency, cart items details and so.
Please note that you have to set details in the payment gateway specific format.

```php
<?php
//src/Acme/PaymentBundle/Controller
namespace AcmeDemoBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function preparePaypalExpressCheckoutPaymentAction()
    {
        $gatewayName = 'your_gateway_name';

        $storage = $this->get('payum')->getStorage('Acme\PaymentBundle\Entity\PaymentDetails');

        /** @var \Acme\PaymentBundle\Entity\PaymentDetails $details */
        $details = $storage->create();
        $details['PAYMENTREQUEST_0_CURRENCYCODE'] = 'USD';
        $details['PAYMENTREQUEST_0_AMT'] = 1.23;
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

That's it. After the payment done you will be redirect to `acme_payment_done` action.
Check [this chapter](../purchase-done-action.md) to find out how this done action could look like.

## Next Step

* [Examples list](../custom-purchase-examples.md).
* [Back to index](../../index.md).