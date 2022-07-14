<?php

namespace Payum\Offline\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Offline\Constants;

class StatusAction implements ActionInterface
{
    public function execute(mixed $request): void
    {
        /** @var GetStatusInterface $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model[Constants::FIELD_STATUS]) {
            $request->markNew();

            return;
        }

        if (Constants::STATUS_PENDING == $model[Constants::FIELD_STATUS]) {
            $request->markPending();

            return;
        }

        if (Constants::STATUS_AUTHORIZED == $model[Constants::FIELD_STATUS]) {
            $request->markAuthorized();

            return;
        }

        if (Constants::STATUS_CAPTURED == $model[Constants::FIELD_STATUS]) {
            $request->markCaptured();

            return;
        }

        if (Constants::STATUS_PAYEDOUT == $model[Constants::FIELD_STATUS]) {
            $request->markPayedout();

            return;
        }

        if (Constants::STATUS_REFUNDED == $model[Constants::FIELD_STATUS]) {
            $request->markRefunded();

            return;
        }

        if (Constants::STATUS_CANCELED == $model[Constants::FIELD_STATUS]) {
            $request->markCanceled();

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
