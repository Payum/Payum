<?php
namespace Payum\Paypal\Masspay\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Payout;

class PayoutAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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