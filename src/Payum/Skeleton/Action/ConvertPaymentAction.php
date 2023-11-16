<?php

namespace Payum\Skeleton\Action;

use LogicException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

class ConvertPaymentAction implements ActionInterface
{
    use GatewayAwareTrait;

    /**
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        throw new LogicException('Not implemented');
    }

    public function supports($request)
    {
        return $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            'array' == $request->getTo()
        ;
    }
}
