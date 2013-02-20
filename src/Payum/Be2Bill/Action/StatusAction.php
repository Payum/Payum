<?php
namespace Payum\Be2Bill\Action;

use Payum\Action\ActionInterface;
use Payum\Bridge\Spl\ArrayObject;
use Payum\Request\StatusRequestInterface;
use Payum\PaymentInstructionAggregateInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Be2Bill\PaymentInstruction;
use Payum\Be2Bill\Api;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = new ArrayObject($request->getModel());        
        if (null === $model['EXECCODE']) {
            $request->markNew();
            
            return;
        }
        if (Api::EXECCODE_SUCCESSFUL === $model['EXECCODE']) {
            $request->markSuccess();

            return;
        }
        
        //TODO add more checks.

        $request->markFailed();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof StatusRequestInterface &&
            (
                is_array($request->getModel()) ||
                $request->getModel() instanceof \ArrayAccess
            )
        ;
    }
}