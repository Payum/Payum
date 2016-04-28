<?php
namespace Payum\Paypal\ProHosted\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ProHosted\Request\Api\GetTransactionDetails;
use Payum\Core\Exception\RequestNotSupportedException;

class GetTransactionDetailsAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request GetTransactionDetails */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null == $model['txn_id']) {
            throw new LogicException('Transaction id Txn_id must be set.');
        }

        $fields                  = new ArrayObject([]);
        $fields['TRANSACTIONID'] = $model['txn_id'];

        $result = $this->api->getTransactionDetails((array) $fields);

        $model->replace($result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetTransactionDetails &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
