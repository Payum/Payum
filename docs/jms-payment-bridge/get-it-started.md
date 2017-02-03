# JMS Payment Bridge. Get it started

Steps:

* [Download libraries](#download-libraries)
* [Configure context](#configure-context)
* [Prepare gateway](#prepare-gateway)

_**Note** : We assume you followed all steps in basic [get it started](../get-it-started.md) and your basic configuration same as described there._

## Download libraries

Run the following command:

```bash
$ php composer.phar require payum/jms-payment-bridge
```

## Configure context

By default PayumBundle knows nothing about jms payment bridge.
To make payum be aware of it you have to add its factory.
Let's suppose you have `AcmePaymentBundle`.
You have to add factory inside its build method:

```php
<?php
namespace Acme\PaymentBundle;

use Payum\Bridge\JMSPayment\DependencyInjection\Factory\Gateway\JmsGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\PayumExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AcmePaymentBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        /** @var  PayumExtension $payumExtension */
        $payumExtension = $container->getExtension('payum');

        $payumExtension->addGatewayFactory(new JmsGatewayFactory);
    }
}
```

Once you added the factory you can configure payum context.

```yaml
jms_payment_core:
    secret:                                               %kernel.secret%

jms_payment_paypal:
    username:                                             %paypal.express_checkout.username%
    password:                                             %paypal.express_checkout.password%
    signature:                                            %paypal.express_checkout.signature%
    debug:                                                true

payum:
    storages:
        JMS\Payment\CoreBundle\Entity\Payment: { doctrine: orm }
    security:
        token_storage:
            Acme\PaymentBundle\Entity\PayumSecurityToken: { doctrine: orm }

    gateways:
        your_payment_name:
            jms_payment_plugin: ~
```

_**Attention**: You have to changed `your_payment_name` to something more descriptive and domain related, for example `post_a_job_with_paypal`._

Not so hard so far, let's continue.

## Prepare payment

Now we are ready to prepare the payment. Here we set price, currency, cart items details and so.
Please note that you have to set details in the jms plugin specific format.

```php
<?php

public function prepareAction(Request $request)
{
    $gatewayName = 'your_payment_name';

    $paymentInstruction = new PaymentInstruction(
        100,
        'USD',
        'paypal_express_checkout'
    );
    $paymentInstruction->setState(PaymentInstruction::STATE_VALID);

    $payment = new Payment($paymentInstruction, 100);

    $this->getDoctrine()->getManager()->persist($paymentInstruction);
    $this->getDoctrine()->getManager()->persist($payment);
    $this->getDoctrine()->getManager()->flush();

    $captureToken = $this->get('payum.security.token_factory')->createCaptureToken(
        $gatewayName,
        $payment,
        'purchase_done'
    );

    $payment->getPaymentInstruction()->getExtendedData()->set(
        'return_url',
        $captureToken->getTargetUrl()
    );
    $payment->getPaymentInstruction()->getExtendedData()->set(
        'cancel_url',
        $captureToken->getTargetUrl()
    );

    //the state manipulations  is needed for saving changes in extended data.
    $oldState = $payment->getPaymentInstruction()->getState();
    $payment->getPaymentInstruction()->setState(PaymentInstruction::STATE_INVALID);

    $this->getDoctrine()->getManager()->persist($paymentInstruction);
    $this->getDoctrine()->getManager()->persist($payment);
    $this->getDoctrine()->getManager()->flush();

    $payment->getPaymentInstruction()->setState($oldState);

    $this->getDoctrine()->getManager()->persist($paymentInstruction);
    $this->getDoctrine()->getManager()->flush();

    return $this->redirect($captureToken->getTargetUrl());
}
```

That's it. After the payment done you will be redirect to `purchase_done` action.
Check [bundle's chapter about done action](../symfony/purchase-done-action.md) to find out how this done action could look like.

* [Back to index](../index.md).
