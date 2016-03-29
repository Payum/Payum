<?php

namespace Payum\Sofort\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Sofort\Request\Api\CreateTransaction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;

class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;
    
    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        /* @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $details['transaction_id']) {
            if (false == $details['success_url'] && $request->getToken()) {
                $details['success_url'] = $request->getToken()->getTargetUrl();
            }
            if (false == $details['abort_url'] && $request->getToken()) {
                $details['abort_url'] = $request->getToken()->getTargetUrl();
            }

            if (false == $details['notification_url'] && $request->getToken() && $this->tokenFactory) {
                $notifyToken = $this->tokenFactory->createNotifyToken(
                    $request->getToken()->getGatewayName(),
                    $request->getToken()->getDetails()
                );

                $details['notification_url'] = $notifyToken->getTargetUrl();
            }

            $this->gateway->execute(new CreateTransaction($details));
        }

        $this->gateway->execute(new Sync($details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
