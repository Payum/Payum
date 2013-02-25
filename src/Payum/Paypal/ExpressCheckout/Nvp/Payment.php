<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Payment as BasePayment;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\StatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\SyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\SetExpressCheckoutAction;

class Payment extends BasePayment
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @return Api
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param Api $api
     *
     * @return \Payum\Paypal\ExpressCheckout\Nvp\Payment
     */
    public static function create(Api $api)
    {
        $payment = new static($api);

        $payment->addAction(new SetExpressCheckoutAction());
        $payment->addAction(new GetExpressCheckoutDetailsAction());
        $payment->addAction(new GetTransactionDetailsAction());
        $payment->addAction(new DoExpressCheckoutPaymentAction());
        
        $payment->addAction(new CaptureAction());
        $payment->addAction(new StatusAction());
        $payment->addAction(new SyncAction());;

        return $payment;
    }
}