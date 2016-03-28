<?php
namespace Payum\Paypal\Masspay\Nvp;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Paypal\Masspay\Nvp\Action\Api\MasspayAction;
use Payum\Paypal\Masspay\Nvp\Action\ConvertPayoutAction;
use Payum\Paypal\Masspay\Nvp\Action\GetPayoutStatusAction;
use Payum\Paypal\Masspay\Nvp\Action\PayoutAction;

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
            'payum.action.api.masspay' => new MasspayAction(),
            'payum.action.convert_payout' => new ConvertPayoutAction(),
            'payum.action.get_payout_status' => new GetPayoutStatusAction(),
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
