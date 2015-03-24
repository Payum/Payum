<?php
namespace Payum\Stripe\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Security\SensitiveValue;

class FillOrderDetailsAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getOrder();

        $details = $order->getDetails();
        $details["amount"] = $order->getTotalAmount();
        $details["currency"] = $order->getCurrencyCode();
        $details["description"] = $order->getDescription();

        if ($card = $order->getCreditCard()) {
            $details["card"] = new SensitiveValue(array(
                'number' => $card->getNumber(),
                'exp_month' => $card->getExpireAt()->format('m'),
                'exp_year' => $card->getExpireAt()->format('Y'),
                'cvc' => $card->getSecurityCode(),
            ));
        }

        $order->setDetails($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof FillOrderDetails;
    }
}
