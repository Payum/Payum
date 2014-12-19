<?php
namespace Payum\Paypal\ProCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Payment;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Paypal\ProCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ProCheckout\Nvp\Action\RefundAction;
use Payum\Paypal\ProCheckout\Nvp\Action\FillOrderDetailsAction;
use Payum\Paypal\ProCheckout\Nvp\Action\StatusAction;

class PaymentFactory extends CorePaymentFactory
{
    /**
     * {@inheritDoc}
     */
    protected function build(Payment $payment, ArrayObject $config)
    {
        $config->validateNotEmpty(array('username', 'password', 'partner', 'vendor', 'tender'));

        $config->defaults(array(
            'sandbox' => true,
        ));

        $paypalConfig = array(
            'username' => $config['username'],
            'password' => $config['password'],
            'partner' => $config['partner'],
            'vendor' => $config['vendor'],
            'tender' =>$config['tender'],
        );

        $config->defaults(array(
            'payum.api' => new Api($paypalConfig, $config['buzz.client']),

            'payum.action.capture' => new CaptureAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.status' => new StatusAction(),
        ));
    }
}
