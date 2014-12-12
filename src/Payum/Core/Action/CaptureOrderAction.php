<?php
namespace Payum\Core\Action;

use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\OrderInterface;
use Payum\Core\Request\Capture;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Request\GetHumanStatus;

class CaptureOrderAction extends GenericOrderAction
{
    /**
     * {@inheritDoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var $order OrderInterface */
        $order = $request->getModel();

        $this->payment->execute($status = new GetHumanStatus($order));
        if ($status->isNew()) {
            $this->payment->execute(new FillOrderDetails($order, $request->getToken()));
        }

        parent::execute($request);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Capture && parent::supports($request);
    }
}
