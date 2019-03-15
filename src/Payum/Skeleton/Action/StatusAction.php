<?php
namespace Payum\Skeleton\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // This action is called when calling: $gateway->execute($status = new GetHumanStatus($model))
        // See: https://github.com/Payum/Payum/blob/master/docs/get-it-started.md#donephp
        //
        // This is where you can mark the request status given your model data.

        /*
        // When a payment has been initiated, mark the request as new
        if ($model['status'] === null) {
            $request->markNew();

            return;
        }

        // When the bank will send you back payment data (e.g.: after a successful or failed payment),
        // then you will able to mark the request with different status.

        if ($model['status'] === 'success') {
            $request->markCaptured();

            return;
        }

        if ($model['status'] === 'error') {
            $request->markFailed();

            return;
        }

        // If the payment's status is unknown, then mark the request as unknown:
        $request->markUnknown();
        */

        throw new \LogicException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
