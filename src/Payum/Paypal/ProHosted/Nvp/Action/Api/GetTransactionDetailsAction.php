<?php

namespace Payum\Paypal\ProHosted\Nvp\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;

class GetTransactionDetailsAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function execute($request): void
    {
        /** @var GetTransactionDetails $request */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null == $model['txn_id']) {
            throw new LogicException('TRANSACTIONID must be set.');
        }

        $fields = new ArrayObject([]);

        $fields['TRANSACTIONID'] = $model['txn_id'];

        $result = $this->api->getTransactionDetails((array) $fields);

        $model->replace($result);
    }

    public function supports($request)
    {
        return $request instanceof GetTransactionDetails &&
            $request->getModel() instanceof ArrayAccess;
    }
}
