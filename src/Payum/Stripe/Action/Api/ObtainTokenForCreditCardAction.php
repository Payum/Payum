<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Stripe\Constants;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateTokenForCreditCard;
use Payum\Stripe\Request\Api\ObtainToken;

class ObtainTokenForCreditCardAction extends GatewayAwareAction implements ApiAwareInterface
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
        /** @var $request ObtainToken */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if ($model['card']) {
            throw new LogicException('Payment already has token set');
        }
        
        $obtainCreditCard = new ObtainCreditCard($request->getToken());
        $obtainCreditCard->setModel($request->getFirstModel());
        $obtainCreditCard->setModel($request->getModel());
        $this->gateway->execute($obtainCreditCard);
        $card = $obtainCreditCard->obtain();

        $local = $model->getArray('local');
        
        $createTokenForCreditCard = new CreateTokenForCreditCard($card);
        $createTokenForCreditCard->setToken((array) $local->getArray('token'));
        
        $this->gateway->execute($createTokenForCreditCard);
        $token = ArrayObject::ensureArrayObject($createTokenForCreditCard->getToken());
        
        $local['token'] = $token->toUnsafeArray();
        $model['local'] = (array) $local;

        if ($token['id']) {
            $model['card'] = $token['id'];
        } else {
            $model['status'] = Constants::STATUS_FAILED;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ObtainToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
