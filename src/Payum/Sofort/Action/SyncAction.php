<?php

namespace Payum\Sofort\Action;

use Payum\Sofort\Request\Api\GetTransactionData;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Sync;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;

class SyncAction extends GatewayAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request Sync
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!isset($model['transaction_id'])) {
            return;
        }

        $this->gateway->execute(new GetTransactionData($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
