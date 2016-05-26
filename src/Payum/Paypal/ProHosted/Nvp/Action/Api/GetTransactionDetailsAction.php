<?php
namespace Payum\Paypal\ProHosted\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;
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

        $fields                  = new ArrayObject([]);
        $fields['TRANSACTIONID'] = $model['txn_id'];

        if (null == $fields['TRANSACTIONID']) {
            throw new LogicException('TRANSACTIONID must be set.');
        }

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
