<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\Payout;

class PayoutPayoutAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Payout $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $payout PayoutInterface */
        $payout = $request->getModel();

        $this->gateway->execute($status = new GetHumanStatus($payout));
        if ($status->isNew()) {
            $this->gateway->execute($convert = new Convert($payout, 'array', $request->getToken()));

            $payout->setDetails($convert->getResult());
        }

        $details = ArrayObject::ensureArrayObject($payout->getDetails());

        $request->setModel($details);
        try {
            $this->gateway->execute($request);
        } finally {
            $payout->setDetails($details);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Payout &&
            $request->getModel() instanceof PayoutInterface
        ;
    }
}
