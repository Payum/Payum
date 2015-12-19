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

        if (null === $details['amount']) {
            throw new LogicException('The parameter "Amount" must be set.');
        }

        if (null === $details['currency_code']) {
            throw new LogicException('The parameter "currency_code" must be set.');
        }

        if (null === $details['reason']) {
            throw new LogicException('The parameter "reason" must be set.');
        }

        $details->replace(
            $this->api->createTransaction((array) $details)
        );

        if (isset($details['payment_url'])) {
            throw new HttpRedirect(
                $details['payment_url']
            );
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
