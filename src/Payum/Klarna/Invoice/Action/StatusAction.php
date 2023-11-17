<?php

namespace Payum\Klarna\Invoice\Action;

use ArrayAccess;
use KlarnaFlags;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{
    /**
     * @param GetStatusInterface $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['error_code']) {
            $request->markFailed();

            return;
        }

        if ($details['canceled']) {
            $request->markCanceled();

            return;
        }

        if ($details['refund_invoice_number']) {
            $request->markRefunded();

            return;
        }

        if ($details['invoice_number']) {
            $request->markCaptured();

            return;
        }

        if (! $details['status']) {
            $request->markNew();

            return;
        }

        if (KlarnaFlags::ACCEPTED == $details['status']) {
            $request->markAuthorized();

            return;
        }

        if (KlarnaFlags::PENDING == $details['status']) {
            $request->markPending();

            return;
        }

        if (KlarnaFlags::DENIED == $details['status']) {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
