<?php
namespace Payum\Bitcoind\Action\Api;

use Payum\Bitcoind\Request\Api\GetNewAddress;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class GetNewAddressAction extends BaseApiAction
{
    /**
     * {@inheritDoc}
     *
     * @param GetNewAddress $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details['address'] = $this->bitcoind->getnewaddress($details['account']);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetNewAddress &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
