<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\CaptureRequest;
use Payum\Stripe\Request\Api\CreateChargeRequest;
use Payum\Stripe\Request\Api\ObtainTokenRequest;

class CaptureAction extends PaymentAwareAction
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

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (is_array($model['card'])) {
            return;
        }

        if (false == $model['card']) {
            $this->payment->execute(new ObtainTokenRequest($model));
        }

        $this->payment->execute(new CreateChargeRequest($model));
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