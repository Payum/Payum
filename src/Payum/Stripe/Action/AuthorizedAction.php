<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Sync;
use Payum\Stripe\Request\Api\CreateCharge;
use Payum\Stripe\Request\Api\ObtainToken;

class AuthorizeAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['id']) {
            $this->payment->execute(new Sync($model));

            return;
        }

        if (false == $model['card']) {
            $this->payment->execute(new ObtainToken($model));
        }

        $model['capture'] = false;
        $this->payment->execute(new CreateCharge($model));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
