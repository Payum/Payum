<?php

namespace Payum\Sofort\Action\Api;

use Payum\Sofort\Request\Api\CreateTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;

class CreateTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request CreateTransaction
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['transaction_id']) {
            throw new LogicException(sprintf('The transaction has already been created for this payment. transaction_id: %s', $details['transaction_id']));
        }

        $details->validateNotEmpty(['amount', 'currency_code', 'reason', 'success_url', 'notification_url']);

        $details->replace($this->api->createTransaction((array) $details));

        if ($details['payment_url']) {
            throw new HttpRedirect($details['payment_url']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateTransaction &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
