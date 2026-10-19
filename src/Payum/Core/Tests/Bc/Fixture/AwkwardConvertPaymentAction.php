<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc\Fixture;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

final class AwkwardConvertPaymentAction implements ActionInterface
{
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var Convert $request */
        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payment->getDetails());
        $details['amount'] = $payment->getTotalAmount();
        $details['currency'] = $payment->getCurrencyCode();
        $details['number'] = $payment->getNumber();

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && 'array' === $request->getTo()
        ;
    }
}
