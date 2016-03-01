<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Security\SensitiveValue;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Core\Request\Capture;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class CaptureAction extends GatewayAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        if (is_numeric($model['RESULT'])) {
            return;
        }

        $cardFields = array('ACCT', 'CVV2', 'EXPDATE');
        if (false == $model->validateNotEmpty($cardFields, false)) {
            try {
                $obtainCreditCard = new ObtainCreditCard($request->getToken());
                $obtainCreditCard->setModel($request->getFirstModel());
                $obtainCreditCard->setModel($request->getModel());
                $this->gateway->execute($obtainCreditCard);
                $card = $obtainCreditCard->obtain();

                $model['EXPDATE'] = SensitiveValue::ensureSensitive($card->getExpireAt()->format('my'));
                $model['ACCT'] = SensitiveValue::ensureSensitive($card->getNumber());
                $model['CVV2'] = SensitiveValue::ensureSensitive($card->getSecurityCode());
            } catch (RequestNotSupportedException $e) {
                throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
            }
        }

        $model->replace($this->api->doSale($model->toUnsafeArray()));
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
