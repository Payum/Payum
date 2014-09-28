<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Action;

use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Paypal\Ipn\Api as IpnApi;

class NotifyAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var IpnApi
     */
    protected $ipnApi;

    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof IpnApi) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->ipnApi = $api;
    }

    /**
     * {@inheritDoc}
     *
     * @param $request Notify
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $this->payment->execute($httpRequest = new GetHttpRequest);
        $ipnDetails = $httpRequest->request;
        if (IpnApi::NOTIFY_INVALID == $this->ipnApi->notifyValidate($ipnDetails)) {
            //TODO add http status to HttpResponse
            throw new LogicException('Invalid IPN request');
        }

        $details = ArrayObject::ensureArrayObject($request->getModel());
        $details->validateNotEmpty(array(
            'PAYMENTINFO_0_TRANSACTIONID',

        ));

        $ipnDetails = ArrayObject::ensureArrayObject($request->getModel());
        $ipnDetails->validateNotEmpty(array(
            'item_name',
            'item_number',
            'payment_status',
            'mc_gross',
            'mc_currency',
            'txn_id',
            'receiver_email',
            'payer_email',
        ));

        // TODO find out how to check seller email match.

        if ($ipnDetails['txn_id'] != $details['PAYMENTINFO_0_TRANSACTIONID']) {
            throw new LogicException(sprintf(
                'The details PAYMENTINFO_0_TRANSACTIONID %s does not match txn_id %s in the ipn request.',
                $details['PAYMENTINFO_0_TRANSACTIONID'],
                $ipnDetails['txn_id']
            ));
        }

        if ($ipnDetails['mc_gross'] != $details['PAYMENTINFO_0_AMT']) {
            throw new LogicException(sprintf(
                'The details PAYMENTINFO_0_AMT %s does not match mc_gross %s in the ipn request.',
                $details['PAYMENTINFO_0_AMT'],
                $ipnDetails['mc_gross']
            ));
        }

        if ($ipnDetails['mc_currency'] != $details['PAYMENTINFO_0_CURRENCYCODE']) {
            throw new LogicException(sprintf(
                'The details PAYMENTINFO_0_CURRENCYCODE %s does not match mc_currency %s in the ipn request.',
                $details['PAYMENTINFO_0_CURRENCYCODE'],
                $ipnDetails['mc_currency']
            ));
        }

        $notifications = $details['notifications'] ?: array();
        $notifications[] = $ipnDetails;

        $details['latest_notification'] = $ipnDetails;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ; 
    }
}
