# Stripe via omnipay

Steps:

* [Download libraries](#download-libraries)
* [Configure context](#configure-context)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/get_it_started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require "payum/omnipay-bridge:*@stable" "omnipay/stripe:~2.0"
```

## Configure context

```yaml
#app/config/config.yml

payum:
    contexts:
        your_context_here:
            omnipay:
                type: Stripe
                options:
                    apiKey: abc123
                    testMode: true
```

_**Note:** You have to changed `your_payment_name` to something more descriptive and domain related, for example `post_a_job_with_omnipay`._

_**Note:** If you have to use onsite payment like paypal express checkout use `omnipay_onsite` factory._

## Prepare payment

Now we are ready to prepare the payment. Here we set price, currency, cart items details and so.
Please note that you have to set details in the payment gateway specific format.

```php
<?php
//src/Acme/PaymentBundle/Controller
namespace AcmeDemoBundle\Controller;

use Payum\Core\Security\SensitiveValue;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function prepareStripePaymentAction(Request $request)
    {
        $paymentName = 'your_payment_name';

        $storage = $this->get('payum')->getStorage('Acme\PaymentBundle\Entity\PaymentDetails');

        /** @var \Acme\PaymentBundle\Entity\PaymentDetails */
        $details = $storage->createModel();
        $details['amount'] = 10;
        
        $storage->updateModel($details);

        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName,
            $details,
            'acme_payment_done' // the route to redirect after capture;
        );

        return $this->redirect($captureToken->getTargetUrl());
    }
}
```

That's it. It will ask user for credit card and convert it to payment specific format. After the payment done you will be redirect to `acme_payment_done` action.
Check [this chapter](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md) to find out how this done action could look like.

If you still able to pass credit card details explicitly:

```php
<?php
use Payum\Core\Security\SensitiveValue;

$details['card'] = new SensitiveValue(array(
    'number' => '5555556778250000',
    'cvv' => 123,
    'expiryMonth' => 6,
    'expiryYear' => 16,
    'firstName' => 'foo',
    'lastName' => 'bar',
));
```

## Next Step

* [Purchase done action](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md).
* [Configuration reference](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/configuration_reference.md).
* [Examples list](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/custom_purchase_examples.md).
* [Back to index](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md).
