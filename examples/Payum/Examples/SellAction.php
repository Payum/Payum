<?php
namespace Payum\Examples;

use Payum\Action\ActionPaymentAware;
use Payum\Request\SimpleSellRequest;

class SellAction extends  ActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {   
        if ($request instanceof AuthorizeRequiredSellRequest) {
            $this->payment->execute(new AuthorizeRequest, false);
        }
        
        //sell code here.
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof SimpleSellRequest;
    }
}
