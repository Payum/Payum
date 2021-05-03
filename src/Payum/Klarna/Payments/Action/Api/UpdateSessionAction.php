<?php


namespace Payum\Klarna\Payments\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Checkout\Action\Api\BaseSessionAction;
use Payum\Klarna\Payments\Model\Session;
use Payum\Klarna\Payments\Request\Api\UpdateSession;


/**
 * TODO: Add description
 *
 * @author Oscar Reimer <oscar.reimer@eit.lth.se>
 */
class UpdateSessionAction extends BaseSessionAction
{

    /**
     * {@inheritDoc}
     *
     * @param UpdateSession $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $this->callWithRetry(function() use ($model, $request) {
            $order = new Session($this->getConnector(), $model['session_id']);
            $data = $model->toUnsafeArray();
            unset($data['location']);
            $order->update($data);
            $request->setSession($order);
        });
    }
    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof UpdateSession;
    }

}