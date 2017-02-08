<?php
namespace Payum\Paypal\Masspay\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PayoutInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

class ConvertPayoutAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PayoutInterface $payout */
        $payout = $request->getSource();

        $this->gateway->execute($currency = new GetCurrency($payout->getCurrencyCode()));
        $divisor = pow(10, $currency->exp);

        $details = ArrayObject::ensureArrayObject($payout->getDetails());
        $details['CURRENCYCODE'] = $payout->getCurrencyCode();
        $details['L_AMT0'] = $payout->getTotalAmount() / $divisor;
        $details['L_NOTE0'] = $payout->getDescription();

        if ($payout->getRecipientEmail()) {
            $details['RECEIVERTYPE'] = 'EmailAddress';
            $details['L_EMAIL0'] = $payout->getRecipientEmail();
        } elseif ($payout->getRecipientId()) {
            $details['RECEIVERTYPE'] = 'UserID';
            $details['L_RECEIVERID0'] = $payout->getRecipientId();
        } else {
            throw new LogicException('Either recipient id or email must be set.');
        }
        
        $request->setResult((array) $details);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PayoutInterface &&
            'array' == $request->getTo()
        ;
    }
}
