<?php
namespace Payum\Examples\Action;

use Payum\Action\PaymentAwareAction;
use Payum\Examples\Model\AuthorizeRequiredModel;
use Payum\Request\CaptureRequest;
use Payum\Examples\Request\AuthorizeRequest;

class CaptureAction extends  PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {   
        /** @var $request CaptureRequest */
        if ($request->getModel() instanceof AuthorizeRequiredModel) {
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
