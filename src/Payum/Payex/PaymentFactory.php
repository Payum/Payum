<?php
namespace Payum\Payex;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\Payment;
use Payum\Core\PaymentFactoryInterface;
use Payum\Payex\Action\AgreementDetailsStatusAction;
use Payum\Payex\Action\Api\AutoPayAgreementAction;
use Payum\Payex\Action\Api\CheckAgreementAction;
use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Action\Api\CheckRecurringPaymentAction;
use Payum\Payex\Action\Api\CreateAgreementAction;
use Payum\Payex\Action\Api\DeleteAgreementAction;
use Payum\Payex\Action\Api\StartRecurringPaymentAction;
use Payum\Payex\Action\Api\StopRecurringPaymentAction;
use Payum\Payex\Action\FillOrderDetailsAction;
use Payum\Payex\Action\PaymentDetailsSyncAction;
use Payum\Payex\Action\Api\CompleteOrderAction;
use Payum\Payex\Action\Api\InitializeOrderAction;
use Payum\Payex\Action\PaymentDetailsCaptureAction;
use Payum\Payex\Action\PaymentDetailsStatusAction;
use Payum\Payex\Action\AutoPayPaymentDetailsCaptureAction;
use Payum\Payex\Action\AutoPayPaymentDetailsStatusAction;
use Payum\Payex\Api\AgreementApi;
use Payum\Payex\Api\OrderApi;
use Payum\Payex\Api\RecurringApi;
use Payum\Payex\Api\SoapClientFactory;

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

        $config['options.required'] = array('accountNumber', 'encryptionKey');

        $config->defaults(array(
            'soap.client_factory' => new SoapClientFactory(),
            'sandbox' => true,

            'payum.api.order' => function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                $payexConfig = array(
                    'accountNumber' => $config['accountNumber'],
                    'encryptionKey' => $config['encryptionKey'],
                    'sandbox' => $config['sandbox'],
                );

                return new OrderApi($config['soap.client_factory'], $payexConfig);
            },
            'payum.api.agreement' => function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                $payexConfig = array(
                    'accountNumber' => $config['accountNumber'],
                    'encryptionKey' => $config['encryptionKey'],
                    'sandbox' => $config['sandbox'],
                );

                return new AgreementApi($config['soap.client_factory'], $payexConfig);
            },
            'payum.api.recurring' => function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                $payexConfig = array(
                    'accountNumber' => $config['accountNumber'],
                    'encryptionKey' => $config['encryptionKey'],
                    'sandbox' => $config['sandbox'],
                );

                return new RecurringApi($config['soap.client_factory'], $payexConfig);
            },

            'payum.action.capture' => new PaymentDetailsCaptureAction(),
            'payum.action.fill_order_details' => new FillOrderDetailsAction(),
            'payum.action.status' => new PaymentDetailsStatusAction(),
            'payum.action.sync' => new PaymentDetailsSyncAction(),
            'payum.action.auto_pay_capture' => new AutoPayPaymentDetailsCaptureAction(),
            'payum.action.auto_pay_status' => new AutoPayPaymentDetailsStatusAction(),

            // agreement actions
            'payum.action.api.agreement_details_status' => new AgreementDetailsStatusAction(),
            'payum.action.api.create_agreement' => new CreateAgreementAction(),
            'payum.action.api.delete_agreement' => new DeleteAgreementAction(),
            'payum.action.api.check_agreement' => new CheckAgreementAction(),
            'payum.action.api.auto_pay_agreement' => new AutoPayAgreementAction(),

            //recurring actions
            'payum.action.api.start_recurring_payment' => new StartRecurringPaymentAction(),
            'payum.action.api.stop_recurring_payment' => new StopRecurringPaymentAction(),
            'payum.action.api.check_recurring_payment' => new CheckRecurringPaymentAction(),

            //order actions
            'payum.action.api.initialize_order' => new InitializeOrderAction(),
            'payum.action.api.complete_order' => new CompleteOrderAction(),
            'payum.action.api.check_order' => new CheckOrderAction(),
        ));

        return (array) $config;
    }
}