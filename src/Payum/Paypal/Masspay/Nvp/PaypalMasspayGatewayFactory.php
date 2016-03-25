<?php
namespace Payum\Paypal\Masspay\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\Masspay\Action\Api\DoMasspayAction;
use Payum\Paypal\Masspay\Action\PayoutAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\ConfirmOrderAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\CreateRecurringPaymentProfileAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\DoCaptureAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\DoMasspayPaymentAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\GetMasspayDetailsAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\GetRecurringPaymentsProfileDetailsAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\GetTransactionDetailsAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\SetMasspayAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\AuthorizeTokenAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\CancelRecurringPaymentsProfileAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\ManageRecurringPaymentsProfileStatusAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\CreateBillingAgreementAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\DoReferenceTransactionAction;
use Payum\Paypal\Masspay\Nvp\Action\Api\UpdateRecurringPaymentProfileAction;
use Payum\Paypal\Masspay\Nvp\Action\AuthorizeAction;
use Payum\Paypal\Masspay\Nvp\Action\CaptureAction;
use Payum\Paypal\Masspay\Nvp\Action\ConvertPaymentAction;
use Payum\Paypal\Masspay\Nvp\Action\NotifyAction;
use Payum\Paypal\Masspay\Nvp\Action\PaymentDetailsStatusAction;
use Payum\Paypal\Masspay\Nvp\Action\PaymentDetailsSyncAction;
use Payum\Paypal\Masspay\Nvp\Action\RecurringPaymentDetailsStatusAction;
use Payum\Paypal\Masspay\Nvp\Action\RecurringPaymentDetailsSyncAction;

class PaypalMasspayGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults(array(
            'payum.factory_name' => 'paypal_masspay_nvp',
            'payum.factory_title' => 'PayPal Masspay',
            
            'payum.action.payout' => new PayoutAction(),
            'payum.action.api.do_masspay' => new DoMasspayAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'username' => '',
                'password' => '',
                'signature' => '',
                'sandbox' => true,
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('username', 'password', 'signature');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $paypalConfig = [
                    'username' => $config['username'],
                    'password' => $config['password'],
                    'signature' => $config['signature'],
                    'sandbox' => $config['sandbox'],
                ];

                return new Api($paypalConfig, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
