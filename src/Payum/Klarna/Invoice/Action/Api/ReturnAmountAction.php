<?php
namespace Payum\Klarna\Invoice\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\ReturnAmount;

class ReturnAmountAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param ReturnAmount $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $klarna = $this->getKlarna();

        try {
            $klarna->returnAmount(
                $details['invoice_number'],
                $details['amount'],
                $details['vat'],
                $details['flags'] ?: \KlarnaFlags::NO_FLAG,
                $details['description']
            );
        } catch (\KlarnaException $e) {
            $this->populateDetailsWithError($details, $e, $request);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ReturnAmount &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
