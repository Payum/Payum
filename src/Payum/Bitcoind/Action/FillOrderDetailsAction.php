<?php
namespace Payum\Bitcoind\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;

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

        if ('BTC' != $order->getCurrencyCode()) {
            throw new InvalidArgumentException(sprintf(
                'The currency code must be BTC if you want to use bitcoins. Now it is %s',
                $order->getCurrencyCode()
            ));
        }

        $details = $order->getDetails();
        $details['amount'] = $order->getTotalAmount();
        $details['description'] = $order->getDescription();
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
