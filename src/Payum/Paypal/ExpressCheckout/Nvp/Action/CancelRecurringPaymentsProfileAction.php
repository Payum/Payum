<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Sync;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatus;

class CancelRecurringPaymentsProfileAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Cancel */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(array('PROFILEID', 'BILLINGPERIOD'));

        $cancelDetails = new ArrayObject([
            'PROFILEID' => $model['PROFILEID'],
            'ACTION' => Api::RECURRINGPAYMENTACTION_CANCEL,
        ]);

        $this->gateway->execute(new ManageRecurringPaymentsProfileStatus($cancelDetails));
        $this->gateway->execute(new Sync($request->getModel()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == ($request instanceof Cancel && $request->getModel() instanceof \ArrayAccess)) {
            return false;
        }

        // it must take into account only recurring payments, common payments must be cancelled by another action.
        $model = $request->getModel();
        return isset($model['BILLINGPERIOD']);
    }
}
