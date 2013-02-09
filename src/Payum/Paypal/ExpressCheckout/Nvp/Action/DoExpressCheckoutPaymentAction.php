<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest;

class DoExpressCheckoutPaymentAction extends ActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request DoExpressCheckoutPaymentRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $instruction = $request->getPaymentInstruction();
        if (false == $instruction->getToken()) {
            throw new LogicException('The token must be set. Have you run SetExpressCheckoutAction?');
        }
        if (false == $instruction->getPayerid()) {
            throw new LogicException('The payerid must be set.');
        }
        if (false == $instruction->getPaymentrequestPaymentaction(0)) {
            throw new LogicException('The zero paymentaction must be set.');
        }
        if (false == $instruction->getPaymentrequestAmt(0)) {
            throw new LogicException('The zero paymentamt must be set.');
        }        
        
        $buzzRequest = new FormRequest();
        $buzzRequest->setFields($instruction->toNvp());

        $response = $this->payment->getApi()->doExpressCheckoutPayment($buzzRequest);

        $instruction->fromNvp($response);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof DoExpressCheckoutPaymentRequest;
    }
}