<?php
namespace Payum\Payex\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Payex\Request\Api\CheckOrder;
use Payum\Core\Request\Sync;
use Payum\Core\Exception\RequestNotSupportedException;

class PaymentDetailsSyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Sync */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['transactionNumber']) {
            $this->gateway->execute(new CheckOrder($request->getModel()));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess &&
            $request->getModel()->offsetExists('transactionNumber')
        ;
    }
}
