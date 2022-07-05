<?php

namespace Payum\Offline\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Payout;
use Payum\Offline\Constants;

class PayoutAction implements ActionInterface
{
    public function execute($request)
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

    public function supports($request)
    {
        return $request instanceof Payout &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
