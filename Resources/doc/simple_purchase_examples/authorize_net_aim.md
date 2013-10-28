# Authorize.NET AIM

Steps:

* [Download libraries](#download-libraries)
* [Configure context](#configure-context)
* [Prepare payment](#prepare-payment)

_**Note** : We assume you followed all steps in [get it started](get-it-started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require "payum/authorize-net-aim:*@stable"
```

## Configure context

```yaml
#app/config/config.yml

payum:
    contexts:
        your_context_here:
            authorize_net_aim:
                api:
                    options:
                        login_id: 'get it from gateway'
                        transaction_key: 'get it from gateway'
                        sandbox: true
            storages:
                Acme\PaymentBundle\Entity\PaymentDetails:
                    doctrine:
                        driver: orm
```

_**Attention**: You have to changed `your_payment_name` to something more descriptive and domain related, for example `post_a_job_with_authorize_net`._

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
    public function prepareAuthorizeNetPaymentAction(Request $request)
    {
        $paymentName = 'your_payment_name';
    
        $storage = $this->get('payum')->getStorageForClass(
            'Acme\PaymentBundle\Entity\PaymentDetails',
            $paymentName
        );
    
        /** @var \Acme\PaymentBundle\Entity\PaymentDetails $paymentDetails */
        $paymentDetails = $storage->createModel();
    
        $paymentDetails['amount'] = 1.23;
        $paymentDetails['clientemail'] = 'user@email.com';
        $paymentDetails['card_Num'] ='1111222233334444';
        $paymentDetails['exp_date'] = '15-11';
        $storage->updateModel($paymentDetails);
        
        $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
            $paymentName,
            $paymentDetails,
            'acme_payment_done' // the route to redirect after capture
        );

        return $this->forward('PayumBundle:Capture:do', array(
            'payum_token' => $captureToken,
        ));
    }
}
```

_**Attention**: The credit card is saved to database in this example. You should consider using custom model or take care of removing sensitive data after purchase._

That's it. After the payment done you will be redirect to `acme_payment_done` action.
Check [this chapter](../purchase_done_action.md) to find out how this done action could look like.

## Next Step

* [Purchase done action](../purchase_done_action.md).
* [Configuration reference](../configuration_reference.md).
* [Back to examples list](../simple_purchase_examples.md).
* [Back to index](../index.md).