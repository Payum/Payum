<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\GetRecurringPaymentsProfileDetails;
use Payum\Core\Request\Sync;
use Payum\Core\Exception\RequestNotSupportedException;

class RecurringPaymentDetailsSyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['PROFILEID']) {
            return;
        }

        $this->gateway->execute(new GetRecurringPaymentsProfileDetails($model));
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

        return isset($model['BILLINGPERIOD']) && null !== $model['BILLINGPERIOD'];
    }
}
