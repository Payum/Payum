<?php
namespace Payum\Be2Bill\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Be2Bill\Api;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        if (null === $model['EXECCODE']) {
            $request->markNew();

            return;
        }

        if (Api::EXECCODE_3DSECURE_IDENTIFICATION_REQUIRED === $model['EXECCODE']) {
            throw new HttpResponse(base64_decode($model['3DSECUREHTML']), 302);
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
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
