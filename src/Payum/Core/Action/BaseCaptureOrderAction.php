<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Security\TokenInterface;

abstract class BaseCaptureOrderAction extends PaymentAwareAction
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
            $this->composeDetails($order, $request->getToken());
        }

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
            $request instanceof Capture &&
            $request->getModel() instanceof OrderInterface
        ;
    }

    /**
     * @param OrderInterface $order
     * @param TokenInterface $token
     */
    abstract protected function composeDetails(OrderInterface $order, TokenInterface $token = null);
}