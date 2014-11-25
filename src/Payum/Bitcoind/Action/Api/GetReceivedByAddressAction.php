<?php
namespace Payum\Bitcoind\Action\Api;

use Payum\Bitcoind\Request\Api\GetNewAddress;
use Payum\Bitcoind\Request\Api\GetReceivedByAddress;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class GetReceivedByAddressAction extends BaseApiAction
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

        $details->validateNotEmpty('address');

        if (false == $details['minconf']) {
            $details['minconf'] = 1;
        }

        $details['received_amount'] = $this->bitcoind->getreceivedbyaddress($details['address'], $details['minconf']);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetReceivedByAddress &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
