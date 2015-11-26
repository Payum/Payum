<?php

namespace Invit\PayumSofortueberweisung;

use Invit\PayumSofortueberweisung\Action\Api\CreateTransactionAction;
use Invit\PayumSofortueberweisung\Action\Api\GetTransactionDataAction;
use Invit\PayumSofortueberweisung\Action\Api\RefundTransactionAction;
use Invit\PayumSofortueberweisung\Action\CaptureAction;
use Invit\PayumSofortueberweisung\Action\NotifyAction;
use Invit\PayumSofortueberweisung\Action\RefundAction;
use Invit\PayumSofortueberweisung\Action\StatusAction;
use Invit\PayumSofortueberweisung\Action\SyncAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as CoreGatewayFactory;
use Payum\Core\GatewayFactoryInterface;

class SofortGatewayFactory implements GatewayFactoryInterface
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
     * @param array                   $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $config = array())
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array) $config));

        $config->defaults(array(
            'payum.factory_name' => 'sofort',
            'payum.factory_title' => 'Sofort',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.refund' => new RefundAction(),

            'payum.action.api.create_transaction' => new CreateTransactionAction(),
            'payum.action.api.get_transaction_data' => new GetTransactionDataAction(),
            'payum.action.api.refund_transaction' => new RefundTransactionAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'config_key' => '',
                'abort_url' => '',
            );
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = array('config_key');

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $paypalConfig = array(
                    'config_key' => $config['config_key'],
                    'abort_url' => $config['abort_url'],
                );

                return new Api($paypalConfig);
            };
        }

        return (array) $config;
    }
}
