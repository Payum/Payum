<?php
namespace Payum\Be2Bill\Action;

use Payum\Be2Bill\Api;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\GetHttpQueryRequest;
use Payum\Core\Request\PostRedirectUrlInteractiveRequest;

class CaptureOnsiteAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false === $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
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

        $getHttpQuery = new GetHttpQueryRequest();
        $this->payment->execute($getHttpQuery);

        //we are back from be2bill site so we have to just update model.
        if (isset($getHttpQuery['EXECCODE'])) {
            $model->replace($getHttpQuery);
        } else {
            throw new PostRedirectUrlInteractiveRequest(
                $this->api->getOnsiteUrl(),
                $this->api->prepareOnsitePayment($model->toUnsafeArray())
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        if (false == $request instanceof CaptureRequest) {
            return false;
        }

        if (false == $request->getModel() instanceof \ArrayAccess) {
            return false;
        }

        $model = $request->getModel();

        return empty($model['CARDCODE']);
    }
}
