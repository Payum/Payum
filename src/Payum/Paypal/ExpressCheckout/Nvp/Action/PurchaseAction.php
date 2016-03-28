<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use League\Uri\Schemes\Http as HttpUri;
use League\Uri\Modifiers\MergeQuery;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Sync;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\ConfirmOrder;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\SetExpressCheckout;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\AuthorizeToken;
use Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoExpressCheckoutPayment;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

abstract class PurchaseAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;
    
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $details->validateNotEmpty('PAYMENTREQUEST_0_PAYMENTACTION');

        $details->defaults(array(
            'AUTHORIZE_TOKEN_USERACTION' => Api::USERACTION_COMMIT,
        ));

        $this->gateway->execute($httpRequest = new GetHttpRequest());
        if (isset($httpRequest->query['cancelled'])) {
            $details['CANCELLED'] = true;

            return;
        }

        if (false == $details['TOKEN']) {
            if (false == $details['RETURNURL'] && $request->getToken()) {
                $details['RETURNURL'] = $request->getToken()->getTargetUrl();
            }

            if (false == $details['CANCELURL'] && $request->getToken()) {
                $details['CANCELURL'] = $request->getToken()->getTargetUrl();
            }

            if (empty($details['PAYMENTREQUEST_0_NOTIFYURL']) && $request->getToken() && $this->tokenFactory) {
                $notifyToken = $this->tokenFactory->createNotifyToken(
                    $request->getToken()->getGatewayName(),
                    $request->getToken()->getDetails()
                );

                $details['PAYMENTREQUEST_0_NOTIFYURL'] = $notifyToken->getTargetUrl();
            }

            if ($details['CANCELURL']) {
                $cancelUri = HttpUri::createFromString($details['CANCELURL']);
                $modifier = new MergeQuery('cancelled=1');
                $cancelUri = $modifier($cancelUri);

                $details['CANCELURL'] = (string) $cancelUri;
            }

            $this->gateway->execute(new SetExpressCheckout($details));

            if ($details['L_ERRORCODE0']) {
                return;
            }
        }

        $this->gateway->execute(new Sync($details));

        if (
            $details['PAYERID'] &&
            Api::CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED == $details['CHECKOUTSTATUS'] &&
            $details['PAYMENTREQUEST_0_AMT'] > 0
        ) {
            if (Api::USERACTION_COMMIT !== $details['AUTHORIZE_TOKEN_USERACTION']) {
                $confirmOrder = new ConfirmOrder($request->getFirstModel());
                $confirmOrder->setModel($request->getModel());

                $this->gateway->execute($confirmOrder);
            }

            $this->gateway->execute(new DoExpressCheckoutPayment($details));
        }

        if (false == $details['PAYERID']) {
            $this->gateway->execute(new AuthorizeToken($details));
        }

        $this->gateway->execute(new Sync($details));
    }
}
