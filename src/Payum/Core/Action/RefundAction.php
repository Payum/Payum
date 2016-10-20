<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\RefundInterface;
use Payum\Core\Request\Refund;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetHumanStatus;

class RefundAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Refund $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $refund RefundInterface */
        $refund = $request->getModel();

        $this->gateway->execute($status = new GetHumanStatus($refund));
        if ($status->isNew()) {
            $this->gateway->execute($convert = new Convert($refund, 'array', $request->getToken()));

            $refund->setDetails($convert->getResult());
        }

        $details = ArrayObject::ensureArrayObject($refund->getDetails());

        $request->setModel($details);
        try {
            $this->gateway->execute($request);
        } finally {
            $refund->setDetails($details);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof RefundInterface
        ;
    }
}
