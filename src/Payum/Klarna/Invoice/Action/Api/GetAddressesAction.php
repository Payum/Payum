<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\GetAddresses;
use Payum\Klarna\Invoice\Request\Api\ReserveAmount;

class GetAddressesAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param GetAddresses $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $klarna = $this->createKlarna();

        try {
            $result = $klarna->getAddresses($details['pno']);

            $details['addresses'] = $result;
        } catch (\KlarnaException $e) {
            $details['error_code'] = $e->getCode();
            $details['error_message'] = $e->getMessage();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetAddresses &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}