<?php
namespace Payum\Be2Bill\Action;

use Payum\Be2Bill\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\RedirectPostInteractiveRequest;

class CaptureOnsiteAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false === $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritdoc}
     *
     * @throws LogicException if the token not set in the instruction.
     * @throws RedirectPostInteractiveRequest if authorization required.
     */
    public function execute($request)
    {
        /** @var $request AuthorizeTokenRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        throw new RedirectPostInteractiveRequest(
            $this->api->getOnsiteUrl(),
            $this->api->getOnsiteData($model->toUnsafeArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
