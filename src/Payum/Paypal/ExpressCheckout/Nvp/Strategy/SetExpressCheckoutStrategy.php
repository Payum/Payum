<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Strategy;

use Buzz\Message\Form\FormRequest;

use Payum\StrategyInterface;
use Payum\ActionInterface;
use Payum\Exception\ActionNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Action\SetExpressCheckoutAction;

class SetExpressCheckoutStrategy implements StrategyInterface
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
    
    public function execute(ActionInterface $action)
    {
        /** @var $action SetExpressCheckoutAction */
        if (false == $this->supports($action)) {
            throw ActionNotSupportedException::createStrategyNotSupported($this, $action);
        }

        $request = new FormRequest;
        $request->setField('PAYMENTREQUEST_0_AMT', $action->getInstruction()->getPaymentrequestNAmt(0));
        
        $response = $this->api->setExpressCheckout($request);
        
        //@TODO implement toNvp\fromNvp
        $action->getInstruction()->fromNvp($response->getContent());
    }

    function supports(ActionInterface $action)
    {
        return $action instanceof SetExpressCheckoutAction;
    }
}