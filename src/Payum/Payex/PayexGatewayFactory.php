<?php
namespace Payum\Payex;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Payex\Action\AgreementDetailsStatusAction;
use Payum\Payex\Action\AgreementDetailsSyncAction;
use Payum\Payex\Action\Api\AutoPayAgreementAction;
use Payum\Payex\Action\Api\CheckAgreementAction;
use Payum\Payex\Action\Api\CheckOrderAction;
use Payum\Payex\Action\Api\CheckRecurringPaymentAction;
use Payum\Payex\Action\Api\CreateAgreementAction;
use Payum\Payex\Action\Api\DeleteAgreementAction;
use Payum\Payex\Action\Api\StartRecurringPaymentAction;
use Payum\Payex\Action\Api\StopRecurringPaymentAction;
use Payum\Payex\Action\ConvertPaymentAction;
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

class PayexGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        if (!class_exists('SoapClient')) {
            throw new \LogicException('You must install "ext-soap" extension.');
        }

        $config['payum.default_options'] = array(
            'account_number' => '',
            'encryption_key' => '',
            'sandbox' => true,
        );
        $config->defaults($config['payum.default_options']);
        $config['payum.required_options'] = array('account_number', 'encryption_key');

        $config->defaults(array(
            'payum.factory_name' => 'payex',
            'payum.factory_title' => 'Payex',

            'soap.client_factory' => new SoapClientFactory(),

            'payum.api.order' => function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $payexConfig = array(
                    'account_number' => $config['account_number'],
                    'encryption_key' => $config['encryption_key'],
                    'sandbox' => $config['sandbox'],
                );

                return new OrderApi($config['soap.client_factory'], $payexConfig);
            },
            'payum.api.agreement' => function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $payexConfig = array(
                    'account_number' => $config['account_number'],
                    'encryption_key' => $config['encryption_key'],
                    'sandbox' => $config['sandbox'],
                );

                return new AgreementApi($config['soap.client_factory'], $payexConfig);
            },
            'payum.api.recurring' => function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $payexConfig = array(
                    'account_number' => $config['account_number'],
                    'encryption_key' => $config['encryption_key'],
                    'sandbox' => $config['sandbox'],
                );

                return new RecurringApi($config['soap.client_factory'], $payexConfig);
            },

            'payum.action.capture' => new PaymentDetailsCaptureAction(),
            'payum.action.status' => new PaymentDetailsStatusAction(),
            'payum.action.sync' => new PaymentDetailsSyncAction(),
            'payum.action.auto_pay_capture' => new AutoPayPaymentDetailsCaptureAction(),
            'payum.action.auto_pay_status' => new AutoPayPaymentDetailsStatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            // agreement actions
            'payum.action.api.agreement_details_status' => new AgreementDetailsStatusAction(),
            'payum.action.api.agreement_details_sync' => new AgreementDetailsSyncAction(),
            'payum.action.api.create_agreement' => new CreateAgreementAction(),
            'payum.action.api.delete_agreement' => new DeleteAgreementAction(),
            'payum.action.api.check_agreement' => new CheckAgreementAction(),
            'payum.action.api.auto_pay_agreement' => new AutoPayAgreementAction(),

            //recurring actions
            'payum.action.api.start_recurring_gateway' => new StartRecurringPaymentAction(),
            'payum.action.api.stop_recurring_gateway' => new StopRecurringPaymentAction(),
            'payum.action.api.check_recurring_gateway' => new CheckRecurringPaymentAction(),

            //order actions
            'payum.action.api.initialize_order' => new InitializeOrderAction(),
            'payum.action.api.complete_order' => new CompleteOrderAction(),
            'payum.action.api.check_order' => new CheckOrderAction(),
        ));
    }
}
