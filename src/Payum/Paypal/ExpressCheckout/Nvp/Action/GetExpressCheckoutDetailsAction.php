<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest;

class GetExpressCheckoutDetailsAction extends  ActionPaymentAware
{
    public function execute($request)
    {
        /** @var $request GetExpressCheckoutDetailsRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $instruction = $request->getInstruction();
        if (false == $instruction->getToken()) {
            throw new LogicException('The token must be set. Have you run SetExpressCheckoutAction?');
        }

        $buzzRequest = new FormRequest();
        $buzzRequest->setField('TOKEN', $instruction->getToken());
        
        $response = $this->payment->getApi()->getExpressCheckoutDetails($buzzRequest);
        
        $instruction->fromNvp($response);
    }

    public function supports($request)
    {
        return $request instanceof GetExpressCheckoutDetailsRequest;
    }
}