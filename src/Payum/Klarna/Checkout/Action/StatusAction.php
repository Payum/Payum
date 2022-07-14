<?php

namespace Payum\Klarna\Checkout\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Klarna\Checkout\Constants;

class StatusAction implements ActionInterface
{
    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['error_code']) {
            $request->markFailed();

            return;
        }

        if ($model['invoice_number']) {
            $request->markCaptured();

            return;
        }

        if ($model['reservation']) {
            $request->markAuthorized();

            return;
        }

        if (false == $model['status'] || Constants::STATUS_CHECKOUT_INCOMPLETE == $model['status']) {
            $request->markNew();

            return;
        }

        if (Constants::STATUS_CHECKOUT_COMPLETE == $model['status']) {
            $request->markPending();

            return;
        }

        $request->markUnknown();
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
