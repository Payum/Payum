<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

class ExecuteSameRequestWithModelDetailsAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param ModelAggregateInterface|ModelAwareInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $request->setModel($request->getModel()->getDetails());

        $this->payment->execute($request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof ModelAggregateInterface &&
            $request instanceof ModelAwareInterface &&
            $request->getModel() instanceof DetailsAggregateInterface
        ;
    }
}