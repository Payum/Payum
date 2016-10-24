<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateCharge;

class CreateChargeAction implements ActionInterface, ApiAwareInterface
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

        if (is_array($model['card'])) {
            throw new LogicException('The token has already been used.');
        }

        if (empty($model['card'])) {
            throw new LogicException('The token has to be set.');
        }

        try {
            $refund = $model['refund'];
            \Stripe::setApiKey($this->keys->getSecretKey());
            if($refund == 'false')
            {
                unset($model['refund']);
                unset($model['id']);
                $charge = \Stripe_Charge::create($model->toUnsafeArray());
                $model->replace($charge->__toArray(true));
            } else {
                $charge = \Stripe_Charge::retrieve($model['id']);
                $charge->refund(array('amount' => $model['amount']));
            }

        } catch (\Stripe_CardError $e) {
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
