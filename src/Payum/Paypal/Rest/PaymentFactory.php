<?php
namespace Payum\Paypal\Rest;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\PaymentFactoryInterface;
use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Paypal\Rest\Action\StatusAction;
use Payum\Paypal\Rest\Action\SyncAction;

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
            'payum.action.sync' => new SyncAction(),
            'payum.action.status' => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['options.required'] = array('client_id', 'client_secret', 'config_path');
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['options.required']);

                define("PP_CONFIG_PATH", $config['config_path']);
                $credential = new OAuthTokenCredential($config['client_id'], $config['client_secret']);
                $config['payum.api'] = new ApiContext($credential);
            };
        }

        return (array) $config;
    }
}
