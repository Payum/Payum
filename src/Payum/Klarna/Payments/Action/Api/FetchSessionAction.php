<?php
namespace Payum\Klarna\Payments\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Klarna\Checkout\Action\Api\BaseSessionAction;
use Payum\Klarna\Payments\Request\Api\FetchSession;

class FetchSessionAction extends BaseSessionAction
{
    /**
     * {@inheritDoc}
     *
     * @param FetchSession $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['location']) {
            throw new LogicException('Location has to be provided to fetch an order');
        }

        $this->callWithRetry(function () use ($model, $request) {
            $session = $this->getSession($this->getConnector());
            $session->setLocation($model['location']);
            $session->fetch();

            $request->setSession($session);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FetchSession;
    }
}
