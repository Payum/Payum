<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Model\ModelAggregateInterface;
use Payum\Core\Model\ModelAwareInterface;

class ExecuteSameRequestWithModelDetailsAction extends GatewayAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param ModelAggregateInterface|ModelAwareInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var DetailsAggregateInterface $model */
        $model = $request->getModel();
        $details = $model->getDetails();

        if (is_array($details)) {
            $details = ArrayObject::ensureArrayObject($details);
        }

        $request->setModel($details);
        try {
            $this->gateway->execute($request);
        } finally {
            if ($model instanceof DetailsAwareInterface) {
                $model->setDetails($details);
            }
        }
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
