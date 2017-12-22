<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Keys;
use Payum\Core\Request\Refund;
use Stripe\Refund as StripeRefund;
use Stripe\Error;
use Stripe\Stripe;
use Payum\Stripe\Constants;

class CreateRefundAction implements ActionInterface, ApiAwareInterface
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
        /** @var $request Refund */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (!@$model['charge']) {
            throw new LogicException('The charge id has to be set.');
        }

        if (isset($model['amount'])
            && (!is_numeric($model['amount']) || $model['amount'] <= 0)
        ) {
            throw new LogicException('The amount is invalid.');
        }

        try {
            Stripe::setApiKey($this->keys->getSecretKey());
            $refund = StripeRefund::create($model->toUnsafeArrayWithoutLocal());
            $model->replace($refund->__toArray(true));
        } catch (Error\Base $e) {
            $model->replace($e->getJsonBody());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Refund &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
