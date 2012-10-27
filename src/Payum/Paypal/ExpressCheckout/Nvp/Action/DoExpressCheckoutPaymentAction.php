<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Action\ActionInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\DoExpressCheckoutPaymentRequest;

class DoExpressCheckoutPaymentAction implements ActionInterface
{
    protected $api;
    
    public function __construct(Api $api) 
    {
        $this->api = $api;
    }
    
    public function execute($request)
    {
        /** @var $request DoExpressCheckoutPaymentRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $instruction = $request->getInstruction();
        if (false == $instruction->getToken()) {
            throw new LogicException('The token must be set. Have you run SetExpressCheckoutAction?');
        }
        if (false == $instruction->getPayerid()) {
            throw new LogicException('The payerid must be set.');
        }
        if (false == $instruction->getPaymentrequestNPaymentaction(0)) {
            throw new LogicException('The zero paymentaction must be set.');
        }
        if (false == $instruction->getPaymentrequestNAmt(0)) {
            throw new LogicException('The zero paymentamt must be set.');
        }        
        
        $buzzRequest = new FormRequest();
        $buzzRequest->setFields($instruction->toNvp());

        $response = $this->api->doExpressCheckoutPayment($buzzRequest);

        $instruction->fromNvp($response);
    }

    public function supports($request)
    {
        return $request instanceof DoExpressCheckoutPaymentRequest;
    }
}