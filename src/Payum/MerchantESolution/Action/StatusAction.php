<?php

namespace Payum\MerchantESolution\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Exception\RequestNotSupportedException;

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

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['status'] && false == $model['card']) {
            $request->markNew();
            return;
        }

        if (false == $model['status'] && $model['card']) {
            if ($model['status'] == 'succeeded') {
                $request->markAuthorized();
                return;
            } else {
                $request->markPending();
                return;
            }
        }

        if (Constants::STATUS_FAILED == $model['status']) {
            $request->markFailed();
            return;
        }

        if ($model['refunded']) {
            $request->markRefunded();
            return;
        }

        if (Constants::STATUS_SUCCEEDED == $model['status'] && $model['captured'] && $model['paid']) {
            $request->markCaptured();
            return;
        }

        if (Constants::STATUS_PAID == $model['status'] && $model['captured'] && $model['paid']) {
            $request->markCaptured();
            return;
        }


        if (Constants::STATUS_SUCCEEDED == $model['status'] && false == $model['captured']) {
            $request->markAuthorized();
            return;
        }

        if (Constants::STATUS_PAID == $model['status'] && false == $model['captured']) {
            $request->markAuthorized();
            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess;
    }
}