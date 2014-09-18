<?php
namespace Payum\Paypal\AdaptivePayments\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\AdaptivePayments\Request\Api\Pay;

class PayAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Pay $request
     */
    public function execute($request)
    {
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->replace($this->api->pay((array) $model));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof Pay &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}