<?php
namespace Payum\Paypal\Masspay\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\Masspay\Request\Api\DoMasspay;

class DoMasspayAction extends GatewayAwareAction
{

    /**
     * {@inheritdoc}
     * 
     * @param DoMasspay $request
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
            $request instanceof DoMasspay &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}