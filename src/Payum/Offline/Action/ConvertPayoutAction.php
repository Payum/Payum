<?php

namespace Payum\Offline\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Convert;
use Payum\Offline\Constants;
use Payum\Offline\OfflineDetails;

/**
 * @deprecated since 2.0, removed in 3.0 with OfflineGatewayFactory. OfflineGateway runs its handlers instead.
 */
class ConvertPayoutAction implements ActionInterface
{
    /**
     * @param Convert $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PayoutInterface $payout */
        $payout = $request->getSource();

        $details = ArrayObject::ensureArrayObject($payout->getDetails());
        OfflineDetails::fillFromPayout($payout, $details);

        $details->defaults([
            Constants::FIELD_PAYOUT => true,
        ]);

        $request->setResult((array) $details);
    }

    public function supports($request)
    {
        return $request instanceof Convert &&
            $request->getSource() instanceof PayoutInterface &&
            'array' === $request->getTo()
        ;
    }
}
