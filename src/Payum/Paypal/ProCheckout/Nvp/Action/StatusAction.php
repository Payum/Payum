<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Action\ActionInterface;
use Payum\Paypal\ProCheckout\Nvp\PaymentInstruction;
use Payum\Domain\InstructionAggregateInterface;
use Payum\Request\BinaryMaskStatusRequest;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class StatusAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var $request \Payum\Request\StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        $request->markSuccess();
    }
   
    public function supports($request)
    {
        return
            $request instanceof BinaryMaskStatusRequest &&
            $request->getModel() instanceof InstructionAggregateInterface &&
            $request->getModel()->getInstruction() instanceof PaymentInstruction
        ;
    }
}
