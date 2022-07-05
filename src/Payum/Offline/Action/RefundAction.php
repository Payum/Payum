<?php

namespace Payum\Offline\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;
use Payum\Offline\Constants;

class RefundAction implements ActionInterface
{
    public function execute($request)
    {
        /** @var Refund $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (Constants::STATUS_CAPTURED == $model[Constants::FIELD_STATUS]) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_REFUNDED;
        }
    }

    public function supports($request)
    {
        return $request instanceof Refund &&
            $request->getModel() instanceof ArrayAccess
            ;
    }
}
