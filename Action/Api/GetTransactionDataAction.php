<?php
namespace Invit\PayumSofort\Action\Api;

use Invit\PayumSofort\Request\Api\GetTransactionData;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;

class GetTransactionDataAction extends  BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request GetTransactionData */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($details['transaction_id'])) {
            throw new LogicException('transaction_id must be set. Have you run CreateTransactionDataAction?');
        }

        $details->replace(
            $this->api->getTransactionData($details['transaction_id'])
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetTransactionData &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
}
