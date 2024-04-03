<?php

namespace Payum\Payex\Action\Api;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Request\Api\StopRecurringPayment;

class StopRecurringPaymentAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = RecurringApi::class;
    }

    public function execute($request): void
    {
        /** @var StopRecurringPayment $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validatedKeysSet([
            'agreementRef',
        ]);

        $result = $this->api->stop((array) $model);

        $model->replace($result);
    }

    public function supports($request)
    {
        return $request instanceof StopRecurringPayment &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
