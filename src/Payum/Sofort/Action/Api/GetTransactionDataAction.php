<?php

namespace Payum\Sofort\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Sofort\Api;
use Payum\Sofort\Request\Api\GetTransactionData;

class GetTransactionDataAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * @param $request GetTransactionData
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $details['transaction_id']) {
            throw new LogicException('The parameter "transaction_id" must be set. Have you run CreateTransactionAction?');
        }

        $details->replace($this->api->getTransactionData($details['transaction_id']));
    }

    public function supports($request)
    {
        return $request instanceof GetTransactionData &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
