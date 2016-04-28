<?php
namespace Payum\Paypal\ProHosted\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;
use Payum\Paypal\ProHosted\Request\Api\GetTransactionDetails;
use Payum\Paypal\ProHosted\Api;

class PaymentDetailsSyncAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Sync $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(['txn_id']);

        $this->gateway->execute(new GetTransactionDetails($model));

        if ($model['ACK'] == Api::ACK_SUCCESS) {
            $model->replace($model);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof Sync) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof \ArrayAccess) {
            return false;
        }
    }
}
