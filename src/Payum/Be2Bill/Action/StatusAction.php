<?php

namespace Payum\Be2Bill\Action;

use ArrayAccess;
use Payum\Be2Bill\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        if (null === $model['EXECCODE']) {
            $request->markNew();

            return;
        }

        if (Api::EXECCODE_SUCCESSFUL === $model['EXECCODE']) {
            $request->markCaptured();

            return;
        }

        if (Api::EXECCODE_TIME_OUT === $model['EXECCODE']) {
            $request->markUnknown();

            return;
        }

        $request->markFailed();
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
