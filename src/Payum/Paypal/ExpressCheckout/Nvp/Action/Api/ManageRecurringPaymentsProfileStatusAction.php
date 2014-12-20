<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ManageRecurringPaymentsProfileStatus;

class ManageRecurringPaymentsProfileStatusAction extends BaseApiAwareAction
{
    /**
     * [@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request ManageRecurringPaymentsProfileStatus */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(array('PROFILEID', 'ACTION'));

        $model->replace(
            $this->api->manageRecurringPaymentsProfileStatus((array) $model)
        );
    }

    /**
     * [@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ManageRecurringPaymentsProfileStatus &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
