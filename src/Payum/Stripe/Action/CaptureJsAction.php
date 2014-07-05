<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\Http\GetRequestRequest;
use Payum\Core\Request\RenderTemplateRequest;
use Payum\Core\Request\Http\ResponseInteractiveRequest;
use Payum\Stripe\Keys;

class CaptureJsAction extends PaymentAwareAction implements ApiAwareInterface
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
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['card']) {
            return;
        }

        $getHttpRequest = new GetRequestRequest;
        $this->payment->execute($getHttpRequest);
        if ($getHttpRequest->method == 'POST' && isset($getHttpRequest->request['stripeToken'])) {
            try {
                \Stripe::setApiKey($this->keys->getSecret());

                $model['card'] = $getHttpRequest->request['stripeToken'];

                $charge = \Stripe_Charge::create((array) $model);

                $model->replace($charge->__toArray(true));
            } catch(\Stripe_CardError $e) {
                $model->replace($e->getJsonBody());
            }

            return;
        }

        $renderTemplate = new RenderTemplateRequest($this->templateName, array(
            'model' => $model,
            'publishable_key' => $this->keys->getPublishable(),
        ));
        $this->payment->execute($renderTemplate);

        throw new ResponseInteractiveRequest($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}