<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SetExpressCheckoutRequest;

class SetExpressCheckoutAction extends ActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request SetExpressCheckoutRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $instruction = $request->getPaymentInstruction();
        if (false == $instruction->getPaymentrequestAmt(0)) {
            throw new LogicException('The zero paymentamt must be set.');
        }

        $buzzRequest = new FormRequest;
        $buzzRequest->setFields($request->getPaymentInstruction()->toNvp());
        
        $response = $this->payment->getApi()->setExpressCheckout($buzzRequest);

        $instruction->fromNvp($response);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof SetExpressCheckoutRequest;
    }
}