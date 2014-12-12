<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\Cancel;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Refund;

class GenericOrderAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param mixed $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $order OrderInterface */
        $order = $request->getModel();

        $details = ArrayObject::ensureArrayObject($order->getDetails());

        try {
            $request->setModel($details);
            $this->payment->execute($request);

            $order->setDetails($details);
            $request->setModel($order);
        } catch (\Exception $e) {
            $order->setDetails($details);
            $request->setModel($order);

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            (
                $request instanceof Capture ||
                $request instanceof Authorize ||
                $request instanceof Notify ||
                $request instanceof Refund ||
                $request instanceof Cancel ||
                $request instanceof GetStatusInterface
            ) &&
            $request->getModel() instanceof OrderInterface
        ;
    }
}
