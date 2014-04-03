<?php
namespace Payum\Offline\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Offline\Constants;
use Payum\Core\Request\CaptureRequest;

class CaptureAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        /** @var \ArrayAccess $model */
        $model = $request->getModel();

        if (isset($model[Constants::FIELD_PAID])) {
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
