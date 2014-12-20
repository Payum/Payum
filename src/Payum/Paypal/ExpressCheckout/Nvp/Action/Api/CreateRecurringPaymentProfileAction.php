<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfile;

class CreateRecurringPaymentProfileAction extends BaseApiAwareAction
{
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateRecurringPaymentProfile */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(array(
            'TOKEN',
            'PROFILESTARTDATE',
            'DESC',
            'BILLINGPERIOD',
            'BILLINGFREQUENCY',
            'AMT',
            'CURRENCYCODE',
        ));

        $model->replace(
            $this->api->createRecurringPaymentsProfile((array) $model)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateRecurringPaymentProfile &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
