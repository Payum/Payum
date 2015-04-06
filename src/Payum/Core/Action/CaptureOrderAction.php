<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Request\GetHumanStatus;

class CaptureOrderAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $order PaymentInterface */
        $order = $request->getModel();

        $this->gateway->execute($status = new GetHumanStatus($order));
        if ($status->isNew()) {
            $this->gateway->execute(new FillOrderDetails($order, $request->getToken()));
        }

        $details = ArrayObject::ensureArrayObject($order->getDetails());

        $request->setModel($details);
        try {
            $this->gateway->execute($request);

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
            $request->getModel() instanceof PaymentInterface
        ;
    }
}
