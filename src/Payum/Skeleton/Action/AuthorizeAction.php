<?php
namespace Payum\Skeleton\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Authorize;
use Payum\Core\Exception\RequestNotSupportedException;

class AuthorizeAction extends GatewayAwareAction
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

        throw new \LogicException('Not implemented');
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
