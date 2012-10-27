<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Action\ActionInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\SetExpressCheckoutRequest;

class SetExpressCheckoutAction implements ActionInterface
{
    /**
     * @var \Payum\Paypal\ExpressCheckout\Nvp\Api
     */
    protected $api;

    /**
     * @param \Payum\Paypal\ExpressCheckout\Nvp\Api $api
     */
    public function __construct(Api $api) 
    {
        $this->api = $api;
    }
    
    public function execute($request)
    {
        /** @var $request SetExpressCheckoutRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $instruction = $request->getInstruction();
        if (false == $instruction->getPaymentrequestNAmt(0)) {
            throw new LogicException('The zero paymentamt must be set.');
        }

        $buzzRequest = new FormRequest;
        $buzzRequest->setField('PAYMENTREQUEST_0_AMT', $request->getInstruction()->getPaymentrequestNAmt(0));
        
        $response = $this->api->setExpressCheckout($buzzRequest);

        $instruction->fromNvp($response);
    }

    public function supports($request)
    {
        return $request instanceof SetExpressCheckoutRequest;
    }
}