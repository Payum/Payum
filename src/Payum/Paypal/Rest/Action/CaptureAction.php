<?php

namespace Payum\Paypal\Rest\Action;

use League\Uri\Http as HttpUri;
use League\Uri\UriModifier;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment as PaypalPayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Rest\ApiContext;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;

class CaptureAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface, GenericTokenFactoryAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct()
    {
        $this->apiClass = ApiContext::class;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request Capture */
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var \ArrayAccess|PaypalPayment $model */
        $model = $request->getModel();

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->query['cancelled'])) {
            if ($model instanceof PaypalPayment) {
                $model->setState('cancelled');
            } else {
                $model['state'] = 'cancelled';
            }

            return;
        }

        if ($model instanceof PaypalPayment) {
            $payment = $model;
        } else {
            $payment = $this->captureArrayAccess($model, $request);
        }

        if (
            false == isset($payment->state) &&
            isset($payment->payer->payment_method) &&
            'paypal' == $payment->payer->payment_method
        ) {
            $payment->create($this->api);

            if ($model instanceof \ArrayAccess) {
                $model->replace($payment->toArray());
            }

            foreach ($payment->links as $link) {
                if ($link->rel == 'approval_url') {
                    throw new HttpRedirect($link->href);
                }
            }
        }

        if (
            false == isset($payment->state) &&
            isset($payment->payer->payment_method) &&
            'credit_card' == $payment->payer->payment_method
        ) {
            $payment->create($this->api);

            if ($model instanceof \ArrayAccess) {
                $model->replace($payment->toArray());
            }
        }

        if (
            true == isset($payment->state) &&
            isset($payment->payer->payment_method) &&
            'paypal' == $payment->payer->payment_method
        ) {
            $this->gateway->execute($httpRequest = new GetHttpRequest());

            $execution = new PaymentExecution();
            $execution->payer_id = $httpRequest->query['PayerID'];

            $payment->execute($execution, $this->api);

            if ($model instanceof \ArrayAccess) {
                $model->replace($payment->toArray());
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            ($request->getModel() instanceof PaypalPayment || $request->getModel() instanceof \ArrayAccess)
        ;
    }

    private function captureArrayAccess(\ArrayAccess $model, Capture $request): PaypalPayment
    {
        if (isset($model['id'])) {
            return PaypalPayment::get($model['id'], $this->api);
        }

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal($model['amount'] / 100);
        $amount->setCurrency($model['currency']);

        $transaction = new Transaction();
        $transaction->setAmount($amount);

        $redirectUrls = new RedirectUrls();
        $returnUrl = $this->tokenFactory->createCaptureToken(
            $request->getToken()->getGatewayName(),
            $request->getToken()->getDetails(),
            $request->getToken()->getAfterUrl()
        )->getTargetUrl();

        $cancelUri = HttpUri::createFromString($returnUrl);
        $redirectUrls->setReturnUrl($returnUrl)
        ->setCancelUrl((string) UriModifier::mergeQuery($cancelUri, 'cancelled=1'));

        $payment = new PaypalPayment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions([$transaction])
            ->setRedirectUrls($redirectUrls);

        return $payment;
    }
}
