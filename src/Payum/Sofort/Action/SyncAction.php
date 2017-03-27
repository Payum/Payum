<?php

namespace Payum\Sofort\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Sofort\Request\Api\GetTransactionData;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Sync;
use Payum\Core\Exception\RequestNotSupportedException;

class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param $request Sync
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if ($details['transaction_id']) {
            $this->gateway->execute(new GetTransactionData($details));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
