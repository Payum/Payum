<?php

namespace Payum\Paypal\Masspay\Nvp\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Payout;
use Payum\Paypal\Masspay\Nvp\Request\Api\Masspay;

class PayoutAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Payout $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['ACK']) {
            $this->gateway->execute(new Masspay($model));
        }
    }

    public function supports($request)
    {
        return $request instanceof Payout &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
