<?php

namespace Payum\Offline\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Legacy\StatusMarker;
use Payum\Core\Request\GetStatusInterface;
use Payum\Offline\OfflineDetails;

/**
 * @deprecated since 2.0, removed in 3.0 with OfflineGatewayFactory. OfflineGateway runs its handlers instead.
 */
class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        /** @var GetStatusInterface $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        StatusMarker::mark($request, OfflineDetails::status($model));
    }

    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
