<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetCreditCardToken;

class GetCreditCardTokenAction implements ActionInterface
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

        $request->token = $model['customer'];
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
