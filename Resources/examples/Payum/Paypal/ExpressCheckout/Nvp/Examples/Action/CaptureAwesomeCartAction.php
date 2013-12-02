<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart; 

class CaptureAwesomeCartAction extends \Payum\Core\Action\PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $cart = $request->getModel();

        $rawCaptureRequest = new CaptureRequest(array(
            'PAYMENTREQUEST_0_AMT' => $cart->getPrice(),
            'PAYMENTREQUEST_0_CURRENCY' => $cart->getCurrency(),
            'RETURNURL' => 'http://foo.com/finishPayment/'.$cart->getId(),
            'CANCELURL' => 'http://foo.com/finishPayment/'.$cart->getId(),
        ));

        $this->payment->execute($rawCaptureRequest);

        $cart->setPaymentDetails($rawCaptureRequest->getModel());
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof AwesomeCart
        ;
    }
}