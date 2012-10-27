<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Buzz\Message\Form\FormRequest;

use Payum\Action\ActionInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\GetExpressCheckoutDetailsRequest;

class GetExpressCheckoutDetailsAction implements ActionInterface
{
    protected $api;
    
    public function __construct(Api $api) 
    {
        $this->api = $api;
    }
    
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
        
        $response = $this->api->getExpressCheckoutDetails($buzzRequest);
        
        $instruction->fromNvp($response);
    }

    public function supports($request)
    {
        return $request instanceof GetExpressCheckoutDetailsRequest;
    }
}