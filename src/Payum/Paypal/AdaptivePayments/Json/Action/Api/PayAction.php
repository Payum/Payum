<?php
namespace Payum\Paypal\AdaptivePayments\Json\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Paypal\AdaptivePayments\Json\Api;
use Payum\Paypal\AdaptivePayments\Json\Request\Api\Pay;

/**
 * @property Api $api
 */
class PayAction extends BaseAction implements GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        
        $model->validateNotEmpty(['actionType', 'cancelUrl', 'currencyCode', 'receiverList', 'requestEnvelope', 'returnUrl']);

        $this->setDefaultDetailLevel($model);

        $request->setResponse($this->api->pay($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Pay &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
