<?php
namespace Payum\Paypal\Masspay\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Payout;

class PayoutAction extends GatewayAwareAction
{

    /**
     * {@inheritdoc}
     * 
     * @param Payout $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        
        
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof Payout &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}