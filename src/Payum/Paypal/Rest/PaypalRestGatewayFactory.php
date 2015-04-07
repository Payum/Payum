<?php
namespace Payum\Paypal\Rest;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as CoreGatewayFactory;
use Payum\Core\GatewayFactoryInterface;
use Payum\Paypal\Rest\Action\CaptureAction;
use Payum\Paypal\Rest\Action\StatusAction;
use Payum\Paypal\Rest\Action\SyncAction;
use Payum\Core\Exception\InvalidArgumentException;

class PaypalRestGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    private $defaultConfig;

    /**
     * @param array $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array) $config));

        $config->defaults(array(
            'payum.factory_name' => 'paypal_rest',
            'payum.factory_title' => 'PayPal Rest',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.status' => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'client_id' => '',
                'client_secret' => '',
                'config_path' => '',
            );
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = array('client_id', 'client_secret', 'config_path');
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                if (!defined('PP_CONFIG_PATH')) {
                    define('PP_CONFIG_PATH', $config['config_path']);
                } elseif (PP_CONFIG_PATH !== $config['config_path']) {
                    throw new InvalidArgumentException(sprintf('Given "config_path" is invalid. Should be equal to the defined "PP_CONFIG_PATH": %s.', PP_CONFIG_PATH));
                }

                $credential = new OAuthTokenCredential($config['client_id'], $config['client_secret']);
                $config['payum.api'] = new ApiContext($credential);
            };
        }

        return (array) $config;
    }
}
