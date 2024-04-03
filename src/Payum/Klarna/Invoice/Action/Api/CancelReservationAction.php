<?php

namespace Payum\Klarna\Invoice\Action\Api;

use ArrayAccess;
use KlarnaException;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Invoice\Request\Api\CancelReservation;

class CancelReservationAction extends BaseApiAwareAction
{
    /**
     * @param CancelReservation $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        $details->validateNotEmpty(['rno']);

        $klarna = $this->getKlarna();

        try {
            $details['canceled'] = $klarna->cancelReservation($details['rno']);
        } catch (KlarnaException $e) {
            $this->populateDetailsWithError($details, $e, $request);
        }
    }

    public function supports($request)
    {
        return $request instanceof CancelReservation &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
