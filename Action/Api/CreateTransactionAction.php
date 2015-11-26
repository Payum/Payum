<?php

namespace Invit\PayumSofortueberweisung\Action\Api;

use Invit\PayumSofortueberweisung\Request\Api\CreateTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpRedirect;

class CreateTransactionAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /* @var $request CreateTransaction */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (null === $details['amount']) {
            throw new LogicException('amount must be set.');
        }

        if (null === $details['currency_code']) {
            throw new LogicException('currency_code must be set.');
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
