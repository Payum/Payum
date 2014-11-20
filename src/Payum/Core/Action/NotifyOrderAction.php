<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Request\Notify;

class NotifyOrderAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Notify $request
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
            $request instanceof Notify &&
            $request->getModel() instanceof OrderInterface
        ;
    }
}
