<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Strategy;

use Payum\StrategyInterface;
use Payum\ActionInterface;
use Payum\Exception\ActionNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp;

class GetExpressCheckoutDetailsStrategy implements StrategyInterface
{
    protected $api;
    
    public function __construct(ExpressCheckout\NvpApi $api) 
    {
        $this->api = $api;
    }
    
    public function execute(ActionInterface $action)
    {
        if (false == $this->supports($action)) {
            throw ActionNotSupportedException::createStrategyNotSupported($this, $action);
        }
        
        // not implemented
    }

    function supports(ActionInterface $action)
    {
        return $action instanceof ExpressCheckout\Action\GetExpressCheckoutDetailsAction;
    }
}