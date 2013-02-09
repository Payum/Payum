<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Request\CaptureRequest;
use Payum\Action\ActionPaymentAware as BaseActionPaymentAware;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SetExpressCheckoutRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest;
use Payum\Paypal\ExpressCheckout\Nvp\PaymentInstruction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class CaptureAction extends BaseActionPaymentAware
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

        /** @var $instruction PaymentInstruction */
        $instruction = $request->getModel();

        try {
            if (false == $instruction->getPaymentrequestPaymentaction(0)) {
                $instruction->setPaymentrequestPaymentaction(0, Api::PAYMENTACTION_SALE);
            }
            
            if (false == $instruction->getToken()) {
                $this->payment->execute(new SetExpressCheckoutRequest($instruction));
                $this->payment->execute(new AuthorizeTokenRequest($instruction));
            }

            $this->payment->execute(new SyncRequest($instruction));
            
            if (
                $instruction->getPayerid() &&  
                Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $instruction->getCheckoutstatus()
            ) {
                $this->payment->execute(new DoExpressCheckoutPaymentRequest($instruction));
            }

            $this->payment->execute(new SyncRequest($instruction));
        } catch (HttpResponseAckNotSuccessException $e) {
            $instruction->clearErrors();
            $instruction->fromNvp($e->getResponse());
        }
    }

    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof PaymentInstruction
        ;
    }
}