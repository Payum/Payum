<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\RefundInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var RefundInterface $refund */
        $refund = $request->getSource();

        $details = ArrayObject::ensureArrayObject($refund->getDetails());
        $details["amount"] = $refund->getRefundedAmount();
        $details["charge"] = $refund->getCurrencyCode();
        $details["reason"] = $refund->getReason();

        $request->setResult((array) $details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof RefundInterface &&
            $request->getTo() == 'array'
        ;
    }
}
