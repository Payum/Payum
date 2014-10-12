# Stripe.js

Steps:

* [Download libraries](#download-libraries)
* [Configure context](#configure-context)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/get_it_started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require "payum/stripe:*@stable"
```

## Configure context

```yaml
#app/config/config.yml

twig:
    paths:
        %kernel.root_dir%/../vendor/payum/payum/src/Payum/Core/Resources/views: PayumCore
        %kernel.root_dir%/../vendor/payum/payum/src/Payum/Stripe/Resources/views: PayumStripe

payum:
    contexts:
        your_context_here:
            stripe_checkout:
                publishable_key: 'get this from gateway'
                secret_key:      'get this from gateway'
```

_**Attention**: You have to changed `your_payment_name` to something more descriptive and domain related, for example `post_a_job_with_stripe`._

## Prepare payment

Now we are ready to prepare the payment. Here we set price, currency, cart items details and so.
Please note that you have to set details in the payment gateway specific format.

```php
<?php
//src/Acme/PaymentBundle/Controller
namespace AcmeDemoBundle\Controller;

use Acme\PaymentBundle\Entity\PaymentDetails;
use Payum\Core\Security\SensitiveValue;
use Symfony\Component\HttpFoundation\Request;

class PaymentController extends Controller
{
    public function prepareStripeJsPaymentAction(Request $request)
    {
        $paymentName = 'your_payment_name';

        $storage = $this->getPayum()->getStorage('Acme\PaymentBundle\Entity\PaymentDetails');

        /** @var PaymentDetails $details */
        $details = $storage->createModel();
        $details["amount"] = 100;
        $details["currency"] = 'USD';
        $details["description"] = 'a description';
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

That's it. After the payment done you will be redirect to `acme_payment_done` action.
Check [this chapter](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md) to find out how this done action could look like.

## Next Step

* [Purchase done action](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md).
* [Configuration reference](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/configuration_reference.md).
* [Examples list](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/custom_purchase_examples.md).
* [Back to index](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md).