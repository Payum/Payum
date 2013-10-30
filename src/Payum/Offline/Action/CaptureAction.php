<?php
namespace Payum\Offline\Action;

use Payum\Action\ActionInterface;
use Payum\Exception\RequestNotSupportedException;
use Payum\Offline\Constants;
use Payum\Request\CaptureRequest;

class CaptureAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var \ArrayAccess $model */
        $model = $request->getModel();

        if ($model[Constants::FIELD_PAID]) {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_SUCCESS;
        } else {
            $model[Constants::FIELD_STATUS] = Constants::STATUS_PENDING;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}