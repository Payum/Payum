<?php
namespace Payum\Paypal\AdaptivePayments\Json\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Paypal\AdaptivePayments\Json\Api;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['Cancelled']) {
            $request->markCanceled();

            return;
        }

        if (false == $model['status']) {
            if ($model['error']) {
                $request->markFailed();

                return;
            }

            $request->markUnknown();

            return;
        }

        switch ($model['status']) {
            case Api::PAYMENT_STATUS_CREATED:
                $request->markNew();

                return;
            case Api::PAYMENT_STATUS_REVERSALERROR:
            case Api::PAYMENT_STATUS_ERROR:
                $request->markFailed();

                return;
            case Api::PAYMENT_STATUS_PROCESSING:
            case Api::PAYMENT_STATUS_PENDING:
                $request->markPending();

                return;
            case Api::PAYMENT_STATUS_COMPLETED:
            case Api::PAYMENT_STATUS_INCOMPLETE;
                $request->markCaptured();

                return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
