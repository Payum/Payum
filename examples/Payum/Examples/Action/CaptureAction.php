<?php
namespace Payum\Examples\Action;

use Payum\Action\ActionPaymentAware;
use Payum\Examples\Model\AuthorizeRequiredSell;
use Payum\Request\CaptureRequest;
use Payum\Examples\Request\AuthorizeRequest;

class CaptureAction extends  ActionPaymentAware
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {   
        /** @var $request CaptureRequest */
        if ($request->getModel() instanceof AuthorizeRequiredSell) {
            $this->payment->execute(new AuthorizeRequest);
        }
        
        //sell code here.
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof CaptureRequest;
    }
}
