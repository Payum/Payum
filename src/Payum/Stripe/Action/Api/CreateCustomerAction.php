<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCharge;
use Payum\Stripe\Request\Api\ObtainToken;
use Stripe\Customer;
use Stripe\Error;
use Stripe\Stripe;


class CreateCustomerAction extends GatewayAwareAction implements ApiAwareInterface
{
    /**
     * @var Keys
     */
    protected $keys;

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
        /** @var $request CreateCharge */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(array('plan'));

        if (false == $model['card']) {
            $this->gateway->execute(new ObtainToken($model));
        }

        try {
            Stripe::setApiKey($this->keys->getSecretKey());

            $charge = Customer::create($model->toUnsafeArray());

            $model->replace($charge->__toArray(true));
        } catch (Error\Card $e) {
            $model->replace($e->getJsonBody());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateCharge &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
