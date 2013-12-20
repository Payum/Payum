<?php
namespace Payum\Be2Bill\Action;

use Payum\Be2Bill\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\PostRedirectUrlInteractiveRequest;
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
     * @throws PostRedirectUrlInteractiveRequest if authorization required.
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        throw new PostRedirectUrlInteractiveRequest(
            $this->api->getOnsiteUrl(),
            $this->api->prepareOnsitePayment($model->toUnsafeArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof CaptureRequest) {
            return false;
        }

        if (false == $request->getModel() instanceof \ArrayAccess) {
            return false;
        }

        return false == empty($model['CARDCODE']);
    }
}
