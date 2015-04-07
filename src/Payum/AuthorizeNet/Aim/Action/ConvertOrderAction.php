<?php
namespace Payum\AuthorizeNet\Aim\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\FillOrderDetails;

class ConvertOrderAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $order */
        $order = $request->getFrom();
        $divisor = pow(10, $order->getCurrencyDigitsAfterDecimalPoint());

        $details = $order->getDetails();
        $details['amount'] = $order->getTotalAmount() / $divisor;
        $details['invoice_num'] = $order->getNumber();
        $details['description'] = $order->getDescription();
        $details['email'] = $order->getClientEmail();
        $details['cust_id'] = $order->getClientId();

        $request->setTo($details);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getFrom() instanceof PaymentInterface
        ;
    }
}
