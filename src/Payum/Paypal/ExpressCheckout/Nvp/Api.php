<?php

namespace Payum\Paypal\ExpressCheckout\Nvp;

use Http\Message\MessageFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\HttpClientInterface;

/**
 * @link https://www.x.com/developers/paypal/documentation-tools/api/getexpresscheckoutdetails-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/doexpresscheckoutpayment-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/setexpresscheckout-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/gettransactiondetails-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/createrecurringpaymentsprofile-api-operation-nvp
 * @link https://www.x.com/developers/paypal/documentation-tools/api/getrecurringpaymentsprofiledetails-api-operation-nvp
 * @link https://developer.paypal.com/webapps/developer/docs/classic/api/merchant/UpdateRecurringPaymentsProfile_API_Operation_NVP/
 *
 * L_ERRORCODE: @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_nvp_errorcodes
 * ACK: @link https://www.x.com/content/paypal-nvp-api-overview
 */
class Api
{
    public const ACK_SUCCESS = 'Success';

    public const ACK_SUCCESS_WITH_WARNING = 'SuccessWithWarning';

    public const ACK_FAILURE = 'Failure';

    public const ACK_FAILUREWITHWARNING = 'FailureWithWarning';

    public const ACK_WARNING = 'Warning';

    public const CHECKOUTSTATUS_PAYMENT_ACTION_NOT_INITIATED = 'PaymentActionNotInitiated';

    public const CHECKOUTSTATUS_PAYMENT_ACTION_FAILED = 'PaymentActionFailed';

    public const CHECKOUTSTATUS_PAYMENT_ACTION_IN_PROGRESS = 'PaymentActionInProgress';

    public const CHECKOUTSTATUS_PAYMENT_COMPLETED = 'PaymentCompleted';

    public const CHECKOUTSTATUS_PAYMENT_ACTION_COMPLETED = 'PaymentActionCompleted';

    /**
     * No status
     */
    public const PAYMENTSTATUS_NONE = 'None';

    /**
     * A reversal has been canceled; for example, when you win a dispute and the funds for the reversal have been returned to you.
     */
    public const PAYMENTSTATUS_CANCELED_REVERSAL = 'Canceled-Reversal';

    /**
     * The payment has been completed, and the funds have been added successfully to your account balance.
     */
    public const PAYMENTSTATUS_COMPLETED = 'Completed';

    /**
     * You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the PendingReason element.
     */
    public const PAYMENTSTATUS_DENIED = 'Denied';

    /**
     * The authorization period for this payment has been reached.
     */
    public const PAYMENTSTATUS_EXPIRED = 'Expired';

    /**
     * The payment has failed. This happens only if the payment was made from your buyer's bank account.
     */
    public const PAYMENTSTATUS_FAILED = 'Failed';

    /**
     * The transaction has not terminated, e.g. an authorization may be awaiting completion.
     */
    public const PAYMENTSTATUS_IN_PROGRESS = 'In-Progress';

    /**
     * The payment has been partially refunded.
     */
    public const PAYMENTSTATUS_PARTIALLY_REFUNDED = 'Partially-Refunded';

    /**
     * The payment is pending. See the PendingReason field for more information.
     */
    public const PAYMENTSTATUS_PENDING = 'Pending';

    /**
     * You refunded the payment.
     */
    public const PAYMENTSTATUS_REFUNDED = 'Refunded';

    /**
     * A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
     */
    public const PAYMENTSTATUS_REVERSED = 'Reversed';

    /**
     *  A payment has been accepted.
     */
    public const PAYMENTSTATUS_PROCESSED = 'Processed';

    /**
     * An authorization for this transaction has been voided.
     */
    public const PAYMENTSTATUS_VOIDED = 'Voided';

    /**
     * The payment has been completed, and the funds have been added successfully to your pending balance.
     */
    public const PAYMENTSTATUS_COMPLETED_FUNDS_HELD = 'Completed-Funds-Held';

    public const PENDINGREASON_AUTHORIZATION = 'authorization';

    /**
     * How you want to obtain payment. When implementing parallel payments, this field is required and must be set to Order. When implementing digital goods, this field is required and must be set to Sale. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive; except for digital goods, which supports single payments only. If the transaction does not include a one-time purchase, this field is ignored. It is one of the following values:
     *
     * Sale – This is a final sale for which you are requesting payment (default).
     */
    public const PAYMENTACTION_SALE = 'Sale';

    /**
     * How you want to obtain payment. When implementing parallel payments, this field is required and must be set to Order. When implementing digital goods, this field is required and must be set to Sale. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive; except for digital goods, which supports single payments only. If the transaction does not include a one-time purchase, this field is ignored. It is one of the following values:
     *
     * Authorization – This payment is a basic authorization subject to settlement with PayPal Authorization and Capture.
     */
    public const PAYMENTACTION_AUTHORIZATION = 'Authorization';

    /**
     * How you want to obtain payment. When implementing parallel payments, this field is required and must be set to Order. When implementing digital goods, this field is required and must be set to Sale. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive; except for digital goods, which supports single payments only. If the transaction does not include a one-time purchase, this field is ignored. It is one of the following values:
     *
     * Order – This payment is an order authorization subject to settlement with PayPal Authorization and Capture.
     */
    public const PAYMENTACTION_ORDER = 'Order';

    /**
     * Payment has not been authorized by the user.
     */
    public const L_ERRORCODE_PAYMENT_NOT_AUTHORIZED = 10485;

    /**
     * This Express Checkout session has expired.
     */
    public const L_ERRORCODE_SESSION_HAS_EXPIRED = 10411;

    /**
     * PayPal displays the shipping address on the PayPal pages.
     */
    public const NOSHIPPING_DISPLAY_ADDRESS = 0;

    /**
     * PayPal does not display shipping address fields whatsoever.
     */
    public const NOSHIPPING_NOT_DISPLAY_ADDRESS = 1;

    /**
     * If you do not pass the shipping address, PayPal obtains it from the buyer’s account profile.
     */
    public const NOSHIPPING_DISPLAY_BUYER_ADDRESS = 2;

    /**
     * You do not require the buyer’s shipping address be a confirmed address.
     * For digital goods, this field is required, and you must set it to 0.
     * Setting this field overrides the setting you specified in your Merchant Account Profile.
     */
    public const REQCONFIRMSHIPPING_NOT_REQUIRED = 0;

    /**
     * You require the buyer’s shipping address be a confirmed address.
     * Setting this field overrides the setting you specified in your Merchant Account Profile.
     */
    public const REQCONFIRMSHIPPING_REQUIRED = 1;

    /**
     * Indicates whether an item is digital or physical. For digital goods, this field is required and must be set to Digital. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive, and m specifies the list item within the payment; except for digital goods, which only supports single payments.
     */
    public const PAYMENTREQUEST_ITERMCATEGORY_DIGITAL = 'Digital';

    /**
     * Indicates whether an item is digital or physical. For digital goods, this field is required and must be set to Digital. You can specify up to 10 payments, where n is a digit between 0 and 9, inclusive, and m specifies the list item within the payment; except for digital goods, which only supports single payments.
     */
    public const PAYMENTREQUEST_ITERMCATEGORY_PHYSICAL = 'Physical';

    /**
     * Indicates whether you would like PayPal to automatically bill the outstanding balance amount in the next billing cycle.
     *
     * PayPal does not automatically bill the outstanding balance.
     */
    public const AUTOBILLOUTAMT_NOAUTOBILL = 'NoAutoBill';

    /**
     * Indicates whether you would like PayPal to automatically bill the outstanding balance amount in the next billing cycle.
     *
     * PayPal automatically bills the outstanding balance.
     */
    public const AUTOBILLOUTAMT_ADDTONEXTBILLING = 'AddToNextBilling';

    public const BILLINGPERIOD_DAY = 'Day';

    public const BILLINGPERIOD_WEEK = 'Week';

    /**
     * For SemiMonth, billing is done on the 1st and 15th of each month.
     */
    public const BILLINGPERIOD_SEMIMONTH = 'SemiMonth';

    public const BILLINGPERIOD_MONTH = 'Month';

    public const BILLINGPERIOD_YEAR = 'Year';

    /**
     * By default, PayPal suspends the pending profile in the event that the initial payment amount fails. You can override this default behavior by setting this field to ContinueOnFailure. Then, if the initial payment amount fails, PayPal adds the failed payment amount to the outstanding balance for this recurring payment profile.
     */
    public const FAILEDINITAMTACTION_CONTINUEONFAILURE = 'ContinueOnFailure';

    /**
     * If this field is not set or you set it to CancelOnFailure, PayPal creates the recurring payment profile, but places it into a pending status until the initial payment completes. If the initial payment clears, PayPal notifies you by IPN that the pending profile has been activated. If the payment fails, PayPal notifies you by IPN that the pending profile has been canceled.
     */
    public const FAILEDINITAMTACTION_CANCELONFAILURE = 'CancelOnFailure';

    public const CREDITCARDTYPE_VISA = 'Visa';

    public const CREDITCARDTYPE_MASTERCARD = 'MasterCard';

    public const CREDITCARDTYPE_DISCOVER = 'Discover';

    public const CREDITCARDTYPE_AMEX = 'Amex';

    /**
     * If the credit card type is Maestro, you must set CURRENCYCODE to GBP. In addition, you must specify either STARTDATE or ISSUENUMBER.
     */
    public const CREDITCARDTYPE_MAESTRO = 'Maestro';

    public const PAYERSTATUS_VERIFIED = 'verified';

    public const PAYERSTATUS_UNVERIFIED = 'unverified';

    /**
     * The recurring payment profile has been successfully created and activated for scheduled payments according the billing instructions from the recurring payments profile.
     */
    public const PROFILESTATUS_ACTIVEPROFILE = 'ActiveProfile';

    /**
     * The system is in the process of creating the recurring payment profile. Please check your IPN messages for an update.
     */
    public const PROFILESTATUS_PENDINGPROFILE = 'PendingProfile';

    /**
     * Type of billing agreement. For recurring payments, this field must be set to RecurringPayments. In this case, you can specify up to ten billing agreements. Other defined values are not valid.
     */
    public const BILLINGTYPE_RECURRING_PAYMENTS = 'RecurringPayments';

    /**
     * Type of billing agreement for reference transactions. You must have permission from PayPal to use this field. This field must be set to one of the following values:
     *
     * PayPal creates a billing agreement for each transaction associated with buyer. You must specify version 54.0 or higher to use this option.
     */
    public const BILLINGTYPE_MERCHANTINITIATEDBILLING = 'MerchantInitiatedBilling';

    /**
     * Type of billing agreement for reference transactions. You must have permission from PayPal to use this field. This field must be set to one of the following values:
     *
     * PayPal creates a single billing agreement for all transactions associated with buyer. Use this value unless you need per-transaction billing agreements. You must specify version 58.0 or higher to use this option.
     */
    public const BILLINGTYPE_MERCHANTINITIATEDBILLINGSINGLEAGREEMENT = 'MerchantInitiatedBilling';

    public const RECURRINGPAYMENTSTATUS_ACTIVE = 'Active';

    public const RECURRINGPAYMENTSTATUS_PENDING = 'Pending';

    public const RECURRINGPAYMENTSTATUS_CANCELLED = 'Cancelled';

    public const RECURRINGPAYMENTSTATUS_SUSPENDED = 'Suspended';

    public const RECURRINGPAYMENTSTATUS_EXPIRED = 'Expired';

    public const RECURRINGPAYMENTSTATUS_REACTIVATE = 'Reactivate';

    public const RECURRINGPAYMENTACTION_CANCEL = 'Cancel';

    public const USERACTION_COMMIT = 'commit';

    public const CMD_EXPRESS_CHECKOUT = '_express-checkout';

    public const CMD_EXPRESS_CHECKOUT_MOBILE = '_express-checkout-mobile';

    public const VERSION = '65.1';

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    protected $options = [
        'username' => null,
        'password' => null,
        'signature' => null,
        'subject' => null,
        'return_url' => null,
        'cancel_url' => null,
        'sandbox' => null,
        'useraction' => null,
        'cmd' => self::CMD_EXPRESS_CHECKOUT,
    ];

    /**
     * @param HttpClientInterface|null $client
     * @param MessageFactory|null      $messageFactory
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $options = ArrayObject::ensureArrayObject($options);
        $options->defaults($this->options);
        $options->validateNotEmpty([
            'username',
            'password',
            'signature',
        ]);

        if (false == is_bool($options['sandbox'])) {
            throw new InvalidArgumentException('The boolean sandbox option must be set.');
        }

        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Require: PAYMENTREQUEST_0_AMT
     *
     * @return array
     */
    public function setExpressCheckout(array $fields)
    {
        if (false == isset($fields['RETURNURL'])) {
            if (false == $this->options['return_url']) {
                throw new RuntimeException('The return_url must be set either to FormRequest or to options.');
            }

            $fields['RETURNURL'] = $this->options['return_url'];
        }

        if (false == isset($fields['CANCELURL'])) {
            if (false == $this->options['cancel_url']) {
                throw new RuntimeException('The cancel_url must be set either to FormRequest or to options.');
            }

            $fields['CANCELURL'] = $this->options['cancel_url'];
        }

        $fields['METHOD'] = 'SetExpressCheckout';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: TOKEN
     *
     * @return array
     */
    public function getExpressCheckoutDetails(array $fields)
    {
        $fields['METHOD'] = 'GetExpressCheckoutDetails';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: TRANSACTIONID
     *
     * @return array
     */
    public function getTransactionDetails(array $fields)
    {
        $fields['METHOD'] = 'GetTransactionDetails';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: STARTDATE
     *
     * @return array
     */
    public function transactionSearch(array $fields)
    {
        $fields['METHOD'] = 'TransactionSearch';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: PAYMENTREQUEST_0_AMT, PAYMENTREQUEST_0_PAYMENTACTION, PAYERID, TOKEN
     *
     * @return array
     */
    public function doExpressCheckoutPayment(array $fields)
    {
        $fields['METHOD'] = 'DoExpressCheckoutPayment';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * @return array
     */
    public function createRecurringPaymentsProfile(array $fields)
    {
        $fields['METHOD'] = 'CreateRecurringPaymentsProfile';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * @return array
     */
    public function updateRecurringPaymentsProfile(array $fields)
    {
        $fields['METHOD'] = 'UpdateRecurringPaymentsProfile';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * @return array
     */
    public function getRecurringPaymentsProfileDetails(array $fields)
    {
        $fields['METHOD'] = 'GetRecurringPaymentsProfileDetails';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * @return array
     */
    public function manageRecurringPaymentsProfileStatus(array $fields)
    {
        $fields['METHOD'] = 'ManageRecurringPaymentsProfileStatus';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: PAYERID, TOKEN
     *
     * @return array
     */
    public function createBillingAgreement(array $fields)
    {
        $fields['METHOD'] = 'CreateBillingAgreement';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: AMT, PAYMENTACTION, REFERENCEID
     *
     * @return array
     */
    public function doReferenceTransaction(array $fields)
    {
        $fields['METHOD'] = 'DoReferenceTransaction';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: AUTHORIZATIONID, AMT, COMPLETETYPE
     *
     * @return array
     */
    public function doCapture(array $fields)
    {
        $fields['METHOD'] = 'DoCapture';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: TRANSACTIONID
     *
     * @return array
     */
    public function refundTransaction(array $fields)
    {
        $fields['METHOD'] = 'RefundTransaction';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * Require: AUTHORIZATIONID
     *
     * @return array
     */
    public function doVoid(array $fields)
    {
        $fields['METHOD'] = 'DoVoid';

        $this->addVersionField($fields);
        $this->addAuthorizeFields($fields);

        return $this->doRequest($fields);
    }

    /**
     * @param string $token
     *
     * @return string
     */
    public function getAuthorizeTokenUrl($token, array $query = [])
    {
        $defaultQuery = array_filter([
            'useraction' => $this->options['useraction'],
            'cmd' => $this->options['cmd'],
            'token' => $token,
        ]);

        $query = array_filter($query);

        return sprintf(
            'https://%s/cgi-bin/webscr?%s',
            $this->options['sandbox'] ? 'www.sandbox.paypal.com' : 'www.paypal.com',
            http_build_query(array_replace($defaultQuery, $query))
        );
    }

    /**
     * @throws HttpException
     *
     * @return array
     */
    protected function doRequest(array $fields)
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $result = [];
        parse_str($response->getBody()->getContents(), $result);
        foreach ($result as &$value) {
            $value = urldecode($value);
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getApiEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://api-3t.sandbox.paypal.com/nvp' :
            'https://api-3t.paypal.com/nvp'
        ;
    }

    protected function addAuthorizeFields(array &$fields)
    {
        $fields['PWD'] = $this->options['password'];
        $fields['USER'] = $this->options['username'];
        $fields['SIGNATURE'] = $this->options['signature'];

        if ($this->options['subject']) {
            $fields['SUBJECT'] = $this->options['subject'];
        }
    }

    protected function addVersionField(array &$fields)
    {
        $fields['VERSION'] = self::VERSION;
    }
}
