<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Http\GetRequestRequest;
use Payum\Core\Request\Http\ResponseInteractiveRequest;
use Payum\Core\Request\RenderTemplateRequest;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\ObtainTokenRequest;

class ObtainTokenAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @var Keys
     */
    protected $keys;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Keys) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->keys = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request ObtainTokenRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['card']) {
            throw new LogicException('The token has already been set.');
        }

        $getHttpRequest = new GetRequestRequest;
        $this->payment->execute($getHttpRequest);
        if ($getHttpRequest->method == 'POST' && isset($getHttpRequest->request['stripeToken'])) {
            $model['card'] = $getHttpRequest->request['stripeToken'];

            return;
        }

        $this->payment->execute($renderTemplate = new RenderTemplateRequest($this->templateName, array(
            'model' => $model,
            'publishable_key' => $this->keys->getPublishable(),
        )));

        throw new ResponseInteractiveRequest($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ObtainTokenRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}