<?php

namespace Payum\Offline\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Payout;
use Payum\Offline\Constants;

class PayoutAction implements ActionInterface
{
    public function execute(mixed $request): void
    {
        /** @var Payout $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model[Constants::FIELD_PAYOUT]) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_PAYEDOUT;
        } else {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_PENDING;
        }
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Payout &&
            $request->getModel() instanceof ArrayAccess
            ;
    }
}
