<?php

namespace Invit\PayumSofortueberweisung\Action\Api;

use Invit\PayumSofortueberweisung\Request\Api\RefundTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Reply\HttpRedirect;

class RefundTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /* @var $request RefundTransaction */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($details['transaction_id'])) {
            throw new LogicException('transaction_id must be set. Have you run CreateTransactionDataAction?');
        }

        if (!isset($details['refund_amount'])) {
            $details['refund_amount'] = $details['amount'];
        }

        $details->replace(
            $this->api->refundTransaction((array) $details)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof RefundTransaction &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
