<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Action\ActionPaymentAware;
use Payum\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Exception\Http\HttpResponseAckNotSuccessException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SaleRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SetExpressCheckoutRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\AuthorizeTokenRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SyncRequest;

class SaleAction extends ActionPaymentAware
{
    public function execute($request)
    {
        /** @var $request SaleRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        try {
            $instruction = $request->getInstruction();
            $instruction->setPaymentrequestNPaymentaction(0, Api::PAYMENTACTION_SALE);
            
            if (false == $instruction->getToken()) {
                $this->payment->execute(new SetExpressCheckoutRequest($instruction));
                $this->payment->execute(new AuthorizeTokenRequest($instruction));
            }
    
            $this->payment->execute(new GetExpressCheckoutDetailsRequest($instruction));
            if ($instruction->getPayerid() && Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $instruction->getCheckoutstatus()) {
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
        return $request instanceof SaleRequest;
    }
}