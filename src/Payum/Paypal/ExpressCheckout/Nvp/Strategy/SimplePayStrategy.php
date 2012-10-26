<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Strategy;

use Payum\Strategy\StrategyPaymentAware;
use Payum\ActionInterface;
use Payum\Action\SimplePayActionInterface;
use Payum\Exception\ActionNotSupportedException;
use Payum\Exception\LogicException;
use Payum\Paypal\ExpressCheckout\Nvp;


class SimplePayStrategy extends StrategyPaymentAware
{
    public function execute(ActionInterface $action)
    {
        if (false == $this->supports($action)) {
            throw ActionNotSupportedException::createStrategyNotSupported($this, $action);
        }
        
        if (false == $instruction = $action->getInstruction()) {
            $action->setInstruction($instruction = new ExpressCheckout\Instruction);
        }
        
        $instruction->setPaymentrequestNAmt(0, $action->getAmount());
        $instruction->setPaymentrequestNCurrencycode(0, $action->getCurrency());

        $this->payment->execute(new ExpressCheckout\Action\DoExpressCheckoutPaymentAction($instruction));
        
        //TODO: handle status.
    }

    public function supports(ActionInterface $action)
    {
        return 
            $action instanceof SimplePayActionInterface && (
               null === $action->getInstruction() ||
                $action->getInstruction() instanceof ExpressCheckout\Instruction
            )
        ;
    }
}