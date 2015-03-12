<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Request\Cancel;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class CancelRecurringPaymentsProfileAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Cancel */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        $model->validateNotEmpty(array('PROFILEID', 'BILLINGPERIOD'));

        $model['ACTION'] = Api::RECURRINGPAYMENTACTION_CANCEL;

        $model->replace(
            $this->api->manageRecurringPaymentsProfileStatus((array) $model)
        );
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
