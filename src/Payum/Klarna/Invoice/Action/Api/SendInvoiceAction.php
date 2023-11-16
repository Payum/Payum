<?php

namespace Payum\Klarna\Invoice\Action\Api;

use ArrayAccess;
use KlarnaException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\SendInvoice;

class SendInvoiceAction extends BaseApiAwareAction
{
    /**
     * @param SendInvoice $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $klarna = $this->getKlarna();

        try {
            $klarna->sendInvoice($details['invoice_number']);
        } catch (KlarnaException $e) {
            $this->populateDetailsWithError($details, $e, $request);
        }
    }

    public function supports($request)
    {
        return $request instanceof SendInvoice &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
