<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;

class CapturePaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $payment PaymentInterface */
        $payment = $request->getModel();

        $this->gateway->execute($status = new GetHumanStatus($payment));
        if ($status->isNew()) {
            $this->gateway->execute($convert = new Convert($payment, 'array', $request->getToken()));

            $payment->setDetails($convert->getResult());
        }

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $request->setModel($details);
        try {
            $this->gateway->execute($request);
        } finally {
            $payment->setDetails($details);
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
