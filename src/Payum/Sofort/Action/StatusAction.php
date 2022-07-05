<?php

namespace Payum\Sofort\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Sofort\Api;

class StatusAction implements ActionInterface
{
    /**
     * @param $request GetStatusInterface
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (! isset($details['status'])
           && isset($details['transaction_id'])
           && isset($details['expires'])
           && $details['expires'] < time()) {
            $request->markExpired();

            return;
        }

        if (! isset($details['transaction_id']) || ! strlen($details['transaction_id'])) {
            $request->markNew();

            return;
        }

        if (! isset($details['status'])) {
            $request->markNew();

            return;
        }

        $subcode = isset($details['statusReason']) ? $details['statusReason'] : null;
        switch ($details['status']) {
            case Api::STATUS_LOSS:
                $request->markFailed();
                break;
            case Api::STATUS_PENDING:
                $request->markPending();
                break;
            case Api::STATUS_RECEIVED:
                switch ($subcode) {
                    case Api::SUB_PARTIALLY:
                        $request->markUnknown();
                        break;
                    case Api::SUB_CREDITED:
                    case Api::SUB_OVERPAYMENT:
                        $request->markCaptured();
                        break;
                }
                break;
            case Api::STATUS_REFUNDED:
                switch ($subcode) {
                    default:
                    case Api::SUB_COMPENSATION:
                        $request->markUnknown();
                        break;
                    case Api::SUB_REFUNDED:
                        $request->markRefunded();
                        break;
                }
                break;
            case Api::STATUS_UNTRACEABLE:
                $request->markCaptured();
                break;
            default:
                $request->markUnknown();
                break;
        }
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
