<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Be2Bill\Api;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request GetStatusInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = new ArrayObject($request->getModel());
        
        if (null === $model['EXECCODE']) {
            $request->markNew();
            
            return;
        }
        
        if (Api::EXECCODE_SUCCESSFUL === $model['EXECCODE']) {
            $request->markCaptured();

            return;
        }
        
        if (Api::EXECCODE_TIME_OUT  === $model['EXECCODE']) {
            $request->markUnknown();
            
            return;
        }

        $request->markFailed();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}