<?php

namespace Payum\Paypal\Rest\Action;

use PayPal\Api\Payment;
use PayPal\Rest\ApiContext;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = ApiContext::class;
    }

    /**
     * {@inheritDoc}
     *
     * @var GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var \ArrayAccess|Payment $model */
        $model = $request->getModel();

        $state = $model instanceof \ArrayAccess ? ($model['state'] ?? null) : $model->state;

        if ('approved' == $state) {
            $request->markCaptured();

            return;
        }

        if ('created' == $state) {
            $request->markPending();

            return;
        }

        if ('cancelled' == $state) {
            $request->markCanceled();

            return;
        }

        if (null == $state) {
            $request->markNew();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            ($request->getModel() instanceof Payment || $request->getModel() instanceof \ArrayAccess)
        ;
    }
}
