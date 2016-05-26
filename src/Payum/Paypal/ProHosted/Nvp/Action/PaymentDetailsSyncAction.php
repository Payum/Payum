<?php
namespace Payum\Paypal\ProHosted\Nvp\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Sync;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ProHosted\Nvp\Api;
use Payum\Paypal\ProHosted\Nvp\Request\Api\GetTransactionDetails;

class PaymentDetailsSyncAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['txn_id']) {
            $this->gateway->execute(new GetTransactionDetails($model));
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

        return true;
    }
}
