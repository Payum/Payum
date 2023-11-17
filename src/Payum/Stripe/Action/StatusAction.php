<?php

namespace Payum\Stripe\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Stripe\Constants;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['error']) {
            $request->markFailed();

            return;
        }

        if (! $model['status'] && ! $model['card']) {
            $request->markNew();

            return;
        }

        if (! $model['status'] && $model['card']) {
            $request->markPending();

            return;
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

        if (Constants::STATUS_SUCCEEDED == $model['status'] && ! $model['captured']) {
            $request->markAuthorized();

            return;
        }
        if (Constants::STATUS_PAID == $model['status'] && ! $model['captured']) {
            $request->markAuthorized();

            return;
        }

        $request->markUnknown();
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
