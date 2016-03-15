<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetCreditCardToken;
use Payum\Stripe\Request\Api\CreateCharge;
use Payum\Stripe\Request\Api\ObtainToken;

class GetCreditCardTokenAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param GetCreditCardToken $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $request->setToken($model['customer']);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetCreditCardToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
