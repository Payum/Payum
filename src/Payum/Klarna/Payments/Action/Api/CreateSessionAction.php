<?php


namespace Payum\Klarna\Payments\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Checkout\Action\Api\BaseSessionAction;
use Payum\Klarna\Payments\Request\Api\CreateSession;

/**
 * TODO: Add description
 *
 * @author Oscar Reimer <oscar.reimer@eit.lth.se>
 */
class CreateSessionAction extends BaseSessionAction
{

    /**
     * {@inheritDoc}
     *
     * @param CreateSession $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->callWithRetry(function () use ($model, $request) {
            $session = $this->getSession($this->getConnector());
            $session->create($model->toUnsafeArray());

            $request->setSession($session);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof CreateSession;
    }

}