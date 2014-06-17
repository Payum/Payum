# Payex

Steps:

* [Download libraries](#download-libraries)
* [Configure context](#configure-context)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](https://github.com/Payum/PayumBundle/blob/master/Resources/doc/get_it_started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require "payum/payex:*@stable"
```

## Configure context

```yaml
#app/config/config.yml

payum:
    contexts:
        your_context_here:
            payex:
                account_number:  'get this from gateway side'
                encryption_key:  'get this from gateway side'
                sandbox: true
```

_**Attention**: You have to changed `your_payment_name` to something more descriptive and domain related, for example `post_a_job_with_payex`._

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
    public function preparePayexPaymentAction()
    {
        $paymentName = 'your_payment_name';

        $storage = $this->getPayum()->getStorage('Acme\PaymentBundle\Entity\PaymentDetails');

        /** @var \Acme\PaymentBundle\Entity\PaymentDetails $paymentDetails */
        $paymentDetails = $storage->createModel();
        $paymentDetails['price'] = $data['amount'] * 100;
        $paymentDetails['priceArgList'] = '';
        $paymentDetails['vat'] = 0;
        $paymentDetails['currency'] = $data['currency'];
        $paymentDetails['orderId'] = 123;
        $paymentDetails['productNumber'] = 123;
        $paymentDetails['purchaseOperation'] = OrderApi::PURCHASEOPERATION_AUTHORIZATION;
        $paymentDetails['view'] = OrderApi::VIEW_CREDITCARD;
        $paymentDetails['description'] = 'a desc';
        $paymentDetails['clientIPAddress'] = $request->getClientIp();
        $paymentDetails['clientIdentifier'] = '';
        $paymentDetails['additionalValues'] = '';
        $paymentDetails['agreementRef'] = '';
        $paymentDetails['clientLanguage'] = 'en-US';
        $storage->updateModel($paymentDetails);

        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName,
            $paymentDetails,
            'acme_payment_done' // the route to redirect after capture;
        );

        $paymentDetails['returnurl'] = $captureToken->getTargetUrl();
        $paymentDetails['cancelurl'] = $captureToken->getTargetUrl();
        $storage->updateModel($paymentDetails);

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