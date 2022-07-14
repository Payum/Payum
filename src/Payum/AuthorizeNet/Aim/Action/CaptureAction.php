<?php

namespace Payum\AuthorizeNet\Aim\Action;

use ArrayAccess;
use Payum\AuthorizeNet\Aim\Bridge\AuthorizeNet\AuthorizeNetAIM;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\ObtainCreditCard;
use Payum\Core\Security\SensitiveValue;

/**
 * @property AuthorizeNetAIM $api
 */
class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;

    public function __construct()
    {
        $this->apiClass = AuthorizeNetAIM::class;
    }

    public function execute(mixed $request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (null != $model['response_code']) {
            return;
        }

        if (false == $model->validateNotEmpty(['card_num', 'exp_date'], false)) {
            try {
                $obtainCreditCard = new ObtainCreditCard($request->getToken());
                $obtainCreditCard->setModel($request->getFirstModel());
                $obtainCreditCard->setModel($request->getModel());
                $this->gateway->execute($obtainCreditCard);
                $card = $obtainCreditCard->obtain();

                $model['exp_date'] = SensitiveValue::ensureSensitive($card->getExpireAt()->format('m/y'));
                $model['card_num'] = SensitiveValue::ensureSensitive($card->getNumber());
                $model['card_code'] = SensitiveValue::ensureSensitive($card->getSecurityCode());
            } catch (RequestNotSupportedException $e) {
                throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCard request.');
            }
        }

        $api = clone $this->api;
        $api->ignore_not_x_fields = true;
        $api->setFields(array_filter($model->toUnsafeArray()));

        $response = $api->authorizeAndCapture();

        $model->replace(get_object_vars($response));
    }

    public function supports(mixed $request): bool
    {
        return $request instanceof Capture &&
            $request->getModel() instanceof ArrayAccess
        ;
    }
}
