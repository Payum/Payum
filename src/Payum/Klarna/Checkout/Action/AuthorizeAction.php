<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\RenderTemplateRequest;
use Payum\Core\Request\Http\ResponseInteractiveRequest;
use Payum\Core\Request\SyncRequest;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrderRequest;

class AuthorizeAction extends PaymentAwareAction
{
    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param string|null $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request \Payum\Core\Request\CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['location']) {
            $createOrderRequest = new CreateOrderRequest($model);
            $this->payment->execute($createOrderRequest);

            $model->replace($createOrderRequest->getOrder()->marshal());
            $model['location'] = $createOrderRequest->getOrder()->getLocation();
        }

        $this->payment->execute(new SyncRequest($model));

        if (Constants::STATUS_CHECKOUT_INCOMPLETE == $model['status']) {
            $renderTemplate = new RenderTemplateRequest($this->templateName, array(
                'snippet' => $model['gui']['snippet']
            ));
            $this->payment->execute($renderTemplate);

            throw new ResponseInteractiveRequest($renderTemplate->getResult());
        }
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