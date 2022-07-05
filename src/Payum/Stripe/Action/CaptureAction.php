<?php

namespace Payum\Stripe\Action;

use ArrayAccess;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Stripe\Request\Api\CreateCharge;
use Payum\Stripe\Request\Api\ObtainToken;

class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['status']) {
            return;
        }

        if ($model['customer']) {
        } else {
            if (false == $model['card']) {
                $obtainToken = new ObtainToken($request->getToken());
                $obtainToken->setModel($model);

                $this->gateway->execute($obtainToken);
            }
        }

        $this->gateway->execute(new CreateCharge($model));
    }

    public function supports($request)
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
