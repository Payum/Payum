<?php
namespace Payum\Stripe\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Security\SensitiveValue;
use Payum\Stripe\Request\Api\CreateToken;
use Payum\Stripe\Request\Api\CreateTokenForCreditCard;

class CreateTokenForCreditCardAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateTokenForCreditCard */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var CreditCardInterface $card */
        $card = $request->getModel();

        $token = ArrayObject::ensureArrayObject($request->getToken());
        $token['object'] = 'card';
        $token['number'] = SensitiveValue::ensureSensitive($card->getNumber());
        $token['exp_month'] = SensitiveValue::ensureSensitive($card->getExpireAt()->format('m'));
        $token['exp_year'] = SensitiveValue::ensureSensitive($card->getExpireAt()->format('Y'));

        if ($card->getSecurityCode()) {
            $token['cvc'] = SensitiveValue::ensureSensitive($card->getSecurityCode());
        }

        $this->gateway->execute(new CreateToken($token));
        
        $request->setToken($token->toUnsafeArray());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateTokenForCreditCard &&
            $request->getModel() instanceof CreditCardInterface
        ;
    }
}
