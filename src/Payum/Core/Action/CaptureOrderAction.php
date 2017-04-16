<?php
namespace Payum\Core\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\DetailsAggregateInterface;
use Payum\Core\Model\DetailsAwareInterface;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Request\PopulateOrderDetails;

class CaptureOrderAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $model DetailsAwareInterface|DetailsAggregateInterface */
        $model = $request->getModel();
        $details = ArrayObject::ensureArrayObject($model->getDetails());

        try {
            $this->payment->execute($status = new GetHumanStatus($model->getDetails()));

            if ($status->isNew()) {
                $this->payment->execute(new PopulateOrderDetails($model));
            }

            $request->setModel($details);
            $this->payment->execute($request);

            $model->setDetails($details);
            $request->setModel($model);
        } catch (\Exception $e) {
            $model->setDetails($details);
            $request->setModel($model);

            throw $e;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof OrderInterface
        ;
    }
}