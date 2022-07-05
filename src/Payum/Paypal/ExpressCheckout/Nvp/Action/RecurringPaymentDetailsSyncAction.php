<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails;

class RecurringPaymentDetailsSyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        /** @var Sync $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['PROFILEID']) {
            return;
        }

        $this->gateway->execute(new GetRecurringPaymentsProfileDetails($model));
    }

    public function supports($request)
    {
        if (false == $request instanceof Sync) {
            return false;
        }

        $model = $request->getModel();
        if (false == $model instanceof ArrayAccess) {
            return false;
        }

        return isset($model['BILLINGPERIOD']) && null !== $model['BILLINGPERIOD'];
    }
}
