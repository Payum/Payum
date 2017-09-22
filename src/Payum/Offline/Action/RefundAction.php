<?php
namespace Payum\Offline\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Refund;
use Payum\Offline\Constants;

class RefundAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Refund */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model[Constants::FIELD_STATUS] == Constants::STATUS_CAPTURED) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_REFUNDED;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
