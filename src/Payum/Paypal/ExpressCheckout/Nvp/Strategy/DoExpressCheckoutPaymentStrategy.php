<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Strategy;

use Buzz\Message\Form\FormRequest;

use Payum\ActionInterface;
use Payum\Strategy\StrategyPaymentAware;
use Payum\Exception\ActionNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Action\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Action\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\AuthorizeTokenAction;

class DoExpressCheckoutPaymentStrategy extends StrategyPaymentAware
{
    protected $api;
    
    public function __construct(Api $api) 
    {
        $this->api = $api;
    }
    
    public function execute(ActionInterface $action)
    {
        /** @var $action DoExpressCheckoutPaymentAction */
        if (false == $this->supports($action)) {
            throw ActionNotSupportedException::createStrategyNotSupported($this, $action);
        }
        
        $instruction = $action->getInstruction();
        if (false == $instruction->getToken()) {
            $this->payment->execute(new SetExpressCheckoutAction($instruction));
            $this->payment->execute(new AuthorizeTokenAction($instruction));
        }

        $request = new FormRequest();
        $request->setField('TOKEN', $instruction->getToken());
        $request->setField('PAYMENTREQUEST_0_AMT', $instruction->getPaymentrequestNAmt(0));
        $request->setField('PAYMENTREQUEST_0_PAYMENTACTION', 'Sale');
        $request->setField('PAYERID', $instruction->getPayerid());
        
        $response = $this->api->doExpressCheckoutPayment($request);
        if (Api::ACK_FAILURE == $response['ACK'] && Api::L_ERRORCODE_PAYMENT_NOT_AUTHORIZED == $response['L_ERRORCODE0']) {
            $this->payment->execute(new AuthorizeTokenAction($instruction, $force = true));
        }

        

        $this->payment->execute(new ExpressCheckout\Action\GetExpressCheckoutDetailsAction($instruction));
        
        //TODO not implemented
    }

    function supports(ActionInterface $action)
    {
        return $action instanceof DoExpressCheckoutPaymentAction;
    }
}