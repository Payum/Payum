<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Stripe\Constants;
use Payum\Stripe\Keys;
use Payum\Stripe\Request\Api\CreateTokenForCreditCard;
use Payum\Stripe\Request\Api\ObtainToken;

/**
 * @param Keys $keys
 * @param Keys $api
 */
class ObtainTokenForCreditCardAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait {
        setApi as _setApi;
    }
    use GatewayAwareTrait;

    /**
     * @deprecated BC will be removed in 2.x. Use $this->api
     *
     * @var Keys
     */
    protected $keys;

    public function __construct()
    {
        $this->apiClass = Keys::class;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        $this->_setApi($api);

        // BC. will be removed in 2.x
        $this->keys = $this->api;
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
