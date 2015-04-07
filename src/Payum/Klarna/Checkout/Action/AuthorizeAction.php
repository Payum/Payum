<?php
namespace Payum\Klarna\Checkout\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Authorize;
use Payum\Core\Request\RenderTemplate;
use Payum\Core\Request\Sync;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\CreateOrder;

class AuthorizeAction extends GatewayAwareAction
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
     *
     * @param Authorize $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['location']) {
            $createOrderRequest = new CreateOrder($model);
            $this->gateway->execute($createOrderRequest);

            $model->replace($createOrderRequest->getOrder()->marshal());
            $model['location'] = $createOrderRequest->getOrder()->getLocation();
        }

        $this->gateway->execute(new Sync($model));

        if (Constants::STATUS_CHECKOUT_INCOMPLETE == $model['status']) {
            $renderTemplate = new RenderTemplate($this->templateName, array(
                'snippet' => $model['gui']['snippet'],
            ));
            $this->gateway->execute($renderTemplate);

            throw new HttpResponse($renderTemplate->getResult());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Authorize &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
