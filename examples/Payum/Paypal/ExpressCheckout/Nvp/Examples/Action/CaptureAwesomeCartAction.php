<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Examples\Action;

use Payum\Request\CaptureRequest;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\ActionPaymentAware;
use Payum\Paypal\ExpressCheckout\Nvp\Examples\Model\AwesomeCart; 

class CaptureAwesomeCartAction extends ActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
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