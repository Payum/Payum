# Paypal express checkout

Steps:

* [Download libraries](#download-libraries)
* [Configure context](#configure-context)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/get_it_started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require "payum/klarna-checkout:@stable"
```

## Configure context

```yaml
#app/config/config.yml

payum:
    contexts:
        your_context_here:
            klarna_checkout:
                api:
                    options:
                        secret:  'get this from gateway side'
                        merchant_id: 'REPLACE WITH YOUR MERCHANT_ID'
                        sandbox: true
            storages:
                Acme\PaymentBundle\Entity\PaymentDetails:
                    doctrine:
                        driver: orm
```

_**Attention**: You have to changed `your_payment_name` to something more descriptive and domain related, for example `post_a_job_with_klarna`._

## Prepare payment

Now we are ready to prepare the payment. Here we set price, currency, cart items details and so on.
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
        $paymentName = 'your_payment_name';

        $storage = $this->get('payum')->getStorageForClass(
            'Acme\PaymentBundle\Entity\PaymentDetails',
            $paymentName
        );

        /** @var \Acme\PaymentBundle\Entity\PaymentDetails $paymentDetails */
        $details = $storage->createModel();
        $details['purchase_country'] = 'SE';
        $details['purchase_currency'] = 'SEK';
        $details['locale'] = 'sv-se';
        $storage->updateModel($details);

        $captureToken = $captureToken = $this->getTokenFactory()->createCaptureToken(
            $paymentName,
            $details,
            'acme_payment_details_view'
        );

        $details['merchant'] = array(
            'terms_uri' => 'http://example.com/terms',
            'checkout_uri' => 'http://example.com/fuck',
            'confirmation_uri' => $captureToken->getTargetUrl(),
            'push_uri' => $this->getTokenFactory()->createNotifyToken($paymentName, $details)->getTargetUrl()
        );
        $details['cart'] = array(
            'items' => array(
                array(
                   'reference' => '123456789',
                   'name' => 'Klarna t-shirt',
                   'quantity' => 2,
                   'unit_price' => 12300,
                   'discount_rate' => 1000,
                   'tax_rate' => 2500
                ),
                array(
                   'type' => 'shipping_fee',
                   'reference' => 'SHIPPING',
                   'name' => 'Shipping Fee',
                   'quantity' => 1,
                   'unit_price' => 4900,
                   'tax_rate' => 2500
                )
            )
        );
        $storage->updateModel($details);

        return $this->redirect($captureToken->getTargetUrl());
    }
}
```

That's it. After the payment done you will be redirect to `acme_payment_done` action.
Check [this chapter](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md) to find out how this done action could look like.

## Next Step

* [Purchase done action](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/purchase_done_action.md).
* [Configuration reference](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/configuration_reference.md).
* [Back to examples list](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/simple_purchase_examples.md).
* [Back to index](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/index.md).