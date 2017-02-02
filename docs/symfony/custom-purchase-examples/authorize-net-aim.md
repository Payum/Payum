# Authorize.NET AIM

Steps:

* [Download libraries](#download-libraries)
* [Configure gateway](#configure-context)
* [Prepare payment](#prepare-payment)

_**Note**: We assume you followed all steps in [get it started](../get-it-started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require "payum/authorize-net-aim"
```

## Configure gateway

```yaml
#app/config/config.yml

payum:
    gateways:
        your_gateway_here:
            factory: authorize_net_aim
            login_id: 'get it from gateway'
            transaction_key: 'get it from gateway'
            sandbox: true
```

_**Attention**: You have to changed `your_gateway_name` to something more descriptive and domain related, for example `post_a_job_with_authorize_net`._

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
        $gatewayName = 'your_gateway_name';
    
        $storage = $this->get('payum')->getStorage('Acme\PaymentBundle\Entity\PaymentDetails');
    
        /** @var \Acme\PaymentBundle\Entity\PaymentDetails $details */
        $details = $storage->create();
    
        $details['amount'] = 1.23;
        $details['clientemail'] = 'user@email.com';
        $storage->update($details);
        
        $captureToken = $this->get('payum')->getTokenFactory()->createCaptureToken(
            $gatewayName,
            $details,
            'acme_gateway_done' // the route to redirect after capture
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

$details['card_Num'] = new SensitiveValue('1111222233334444');
$details['exp_date'] = new SensitiveValue('15-11');
```

## Next Step

* [Examples list](../custom-purchase-examples.md).
* [Back to index](../../index.md).