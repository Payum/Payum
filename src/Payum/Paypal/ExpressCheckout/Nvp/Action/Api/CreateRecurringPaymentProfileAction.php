<?php

namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\ExpressCheckout\Nvp\Api;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\CreateRecurringPaymentProfile;

class CreateRecurringPaymentProfileAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    public function execute($request): void
    {
        /** @var CreateRecurringPaymentProfile $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty([
            'TOKEN',
            'PROFILESTARTDATE',
            'DESC',
            'BILLINGPERIOD',
            'BILLINGFREQUENCY',
            'AMT',
            'CURRENCYCODE',
        ]);

        $model->replace(
            $this->api->createRecurringPaymentsProfile((array) $model)
        );
    }

    public function supports($request)
    {
        return $request instanceof CreateRecurringPaymentProfile &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
