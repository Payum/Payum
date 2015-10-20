<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayInterface;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;

class ConfirmOrderAction extends BaseApiAwareAction implements GatewayAwareInterface
{
    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @param GatewayInterface $gateway
     */
    public function setGateway(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request SetExpressCheckout */
        RequestNotSupportedException::assertSupports($this, $request);

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if ('POST' == $httpRequest->method && false == empty($httpRequest->request['confirm'])) {
            return;
        }

        $renderTemplate = new RenderTemplate($this->templateName, array(
            'model' => ArrayObject::ensureArrayObject($request->getModel()),
            'firstModel' => $request->getFirstModel(),
        ));
        $this->gateway->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ConfirmOrder &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
