<?php
namespace Payum\Paypal\ExpressCheckout\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\PaymentFactoryInterface;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoExpressCheckoutPaymentAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetExpressCheckoutDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\SetExpressCheckoutAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\CaptureAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\FillOrderDetailsAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\NotifyAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\ExpressCheckout\Nvp\Action\RecurringPaymentDetailsSyncAction;

class PaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $corePaymentFactory;

    /**
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->corePaymentFactory = $corePaymentFactory ?: new CorePaymentFactory();
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->corePaymentFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->corePaymentFactory->createConfig());

        $config->defaults(array(
            'payum.action.capture' => new CaptureAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new PaymentDetailsStatusAction(),
            'payum.action.sync' => new PaymentDetailsSyncAction(),
            'payum.action.recurring_status' => new RecurringPaymentDetailsStatusAction(),
            'payum.action.recurring_sync' => new RecurringPaymentDetailsSyncAction(),

            'payum.action.api.set_express_checkout' => new SetExpressCheckoutAction(),
            'payum.action.api.get_express_checkout_details' => new GetExpressCheckoutDetailsAction(),
            'payum.action.api.get_transaction_details' => new GetTransactionDetailsAction(),
            'payum.action.api.do_express_checkout_payment' => new DoExpressCheckoutPaymentAction(),
            'payum.action.api.create_recurring_payment_profile' => new CreateRecurringPaymentProfileAction(),
            'payum.action.api.get_recurring_payments_profile_details' => new GetRecurringPaymentsProfileDetailsAction(),
            'payum.action.api.manage_recurring_payments_profile_status' => new ManageRecurringPaymentsProfileStatusAction(),
            'payum.action.api.create_billing_agreement' => new CreateBillingAgreementAction(),
            'payum.action.api.do_reference_transaction' => new DoReferenceTransactionAction(),
            'payum.action.api.authorize_token' => new AuthorizeTokenAction(),
        ));

        if (false == $config['payum.api']) {
            $config['options.required'] = array('username', 'password', 'signature');

            $config->defaults(array(
                'sandbox' => true,
            ));

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                $paypalConfig = array(
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'signature' => $config['signature'],
                    'sandbox' => $config['sandbox'],
                );

                return new Api($paypalConfig, $config['buzz.client']);
            };
        }

        return (array) $config;
    }
}
