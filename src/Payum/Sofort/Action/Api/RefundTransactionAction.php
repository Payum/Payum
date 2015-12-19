<?php

namespace Payum\Sofort\Action\Api;

use Payum\Sofort\Request\Api\RefundTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;

class RefundTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request RefundTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($details['transaction_id'])) {
            throw new LogicException('The parameter "transaction_id" must be set. Have you run CreateTransactionAction?');
        }

        if (!isset($details['refund_amount'])) {
            if (!isset($details['amount'])) {
                throw new LogicException('One of the parameters "refund_amount" or "amount" must be set.');
            }

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
