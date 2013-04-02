<?php
namespace Payum\Action;

use Payum\Exception\RequestNotSupportedException;
use Payum\Model\DetailsAggregateInterface;
use Payum\Request\StatusRequestInterface;

class StatusDetailsAggregatedModelAction extends PaymentAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request StatusRequestInterface */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        
        $model = $request->getModel();
        
        $request->setModel($model->getDetails());
        $this->payment->execute($request);

        $request->setModel($model);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof StatusRequestInterface &&
            $request->getModel() instanceof DetailsAggregateInterface && 
            $request->getModel()->getDetails()
        ;
    }
}