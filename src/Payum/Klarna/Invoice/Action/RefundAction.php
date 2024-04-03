<?php

namespace Payum\Klarna\Invoice\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Refund;
use Payum\Klarna\Invoice\Request\Api\CreditPart;

class RefundAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Refund $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['refund_invoice_number']) {
            return;
        }

        $details->validateNotEmpty(['invoice_number']);

        $this->gateway->execute(new CreditPart($details));
    }

    public function supports($request)
    {
        return $request instanceof Refund &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
