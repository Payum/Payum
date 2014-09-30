<?php
namespace Payum\Paypal\AdaptivePayments\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\AdaptivePayments\Request\Api\PaymentDetails;

class PaymentDetailsAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param PaymentDetails $request
     */
    public function execute($request)
    {
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty('payKey');

        $model->replace($this->api->paymentDetails($model['payKey']));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof PaymentDetails &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}