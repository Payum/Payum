<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\UpdateRecurringPaymentProfile;

class UpdateRecurringPaymentProfileAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request UpdateRecurringPaymentProfile */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(array('PROFILEID'));

        $model->replace(
            $this->api->updateRecurringPaymentsProfile((array) $model)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof UpdateRecurringPaymentProfile &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
