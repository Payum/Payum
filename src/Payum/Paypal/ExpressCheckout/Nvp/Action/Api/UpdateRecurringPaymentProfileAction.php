<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\UpdateRecurringPaymentProfile;

class UpdateRecurringPaymentProfileAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function execute($request)
    {
        /** @var UpdateRecurringPaymentProfile $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(['PROFILEID']);

        $model->replace(
            $this->api->updateRecurringPaymentsProfile((array) $model)
        );
    }

    public function supports($request)
    {
        return $request instanceof UpdateRecurringPaymentProfile &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
