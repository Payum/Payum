<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\FillOrderDetails;
use Payum\Core\Security\GenericTokenFactoryInterface;

class FillOrderDetailsAction implements ActionInterface
{
    /**
     * @var GenericTokenFactoryInterface
     */
    protected $tokenFactory;

    /**
     * @param GenericTokenFactoryInterface $tokenFactory
     */
    public function __construct(GenericTokenFactoryInterface $tokenFactory = null)
    {
        $this->tokenFactory = $tokenFactory;
    }

    /**
     * {@inheritDoc}
     *
     * @param FillOrderDetails $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $order = $request->getOrder();
        $divisor = pow(10, $order->getCurrencyDigitsAfterDecimalPoint());

        $details = $order->getDetails();
        $details['INVNUM'] = $order->getNumber();
        $details['PAYMENTREQUEST_0_CURRENCYCODE'] = $order->getCurrencyCode();
        $details['PAYMENTREQUEST_0_AMT'] = $order->getTotalAmount() / $divisor;

        if (empty($details['PAYMENTREQUEST_0_NOTIFYURL']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getPaymentName(),
                $request->getToken()->getDetails()
            );

            $details['PAYMENTREQUEST_0_NOTIFYURL'] = $notifyToken->getTargetUrl();
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
