<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\StrategyInterface;
use Payum\ActionInterface;
use Payum\Action\RedirectUrlAction;
use Payum\Exception\ActionNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp\Action\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class AuthorizeTokenStrategy implements StrategyInterface
{
    protected $api;
    
    public function __construct(Api $api)
    {
        $this->api = $api;
    }
    
    public function execute(ActionInterface $action)
    {
        /** @var $action AuthorizeTokenAction */
        if (false == $this->supports($action)) {
            throw ActionNotSupportedException::createStrategyNotSupported($this, $action);
        }
        
        if (false == $token = $action->getInstruction()->getToken()) {
            throw new LogicException('The token must be set before this execute. Have SetExpressCheckoutStrategy done its job?');
        }
        
        //payer id is set after the user authorize the token.  
        if (false == $action->getInstruction()->getPayerid() || $action->isForced()) {
            throw new RedirectUrlAction($this->api->getAuthorizeTokenUrl($token));
        }
    }

    public function supports(ActionInterface $action)
    {
        return $action instanceof AuthorizeTokenAction;
    }
}