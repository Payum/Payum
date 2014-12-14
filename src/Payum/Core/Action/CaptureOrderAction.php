<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Request\GetHumanStatus;

class CaptureOrderAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $order OrderInterface */
        $order = $request->getModel();

        $this->payment->execute($status = new GetHumanStatus($order));
        if ($status->isNew()) {
            $this->payment->execute(new FillOrderDetails($order, $request->getToken()));
        }

        $details = ArrayObject::ensureArrayObject($order->getDetails());

        $request->setModel($details);
        try {
            $this->payment->execute($request);

            $order->setDetails($details);
        } catch (\Exception $e) {
            $order->setDetails($details);

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof OrderInterface
        ;
    }
}
