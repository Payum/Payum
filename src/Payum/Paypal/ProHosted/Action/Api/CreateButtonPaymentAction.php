<?php
namespace Payum\Paypal\ProHosted\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ProHosted\Request\Api\CreateButtonPayment;
use Payum\Core\Exception\RequestNotSupportedException;

class CreateButtonPaymentAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateButtonPayment */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $result = $this->api->doCreateButton((array) $model);

        $model->replace($result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateButtonPayment &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
