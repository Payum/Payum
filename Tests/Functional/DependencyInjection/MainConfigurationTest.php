<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\AuthorizeNetAimGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\Be2BillDirectGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\Be2BillOffsiteGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaCheckoutGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\KlarnaInvoiceGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayDirectGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\OmnipayOffsiteGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\PaypalProCheckoutNvpGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeCheckoutGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\StripeJsGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Gateway\PaypalExpressCheckoutNvpGatewayFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;

class MainConfigurationTest extends  \PHPUnit_Framework_TestCase
{
    protected $gatewayFactories = array();

    protected $storageFactories = array();
    
    protected function setUp()
    {
        $this->gatewayFactories = array(
            new PaypalExpressCheckoutNvpGatewayFactory,
            new PaypalProCheckoutNvpGatewayFactory,
            new AuthorizeNetAimGatewayFactory,
            new Be2BillDirectGatewayFactory,
            new Be2BillOffsiteGatewayFactory(),
            new OmnipayDirectGatewayFactory,
            new OmnipayOffsiteGatewayFactory(),
            new KlarnaCheckoutGatewayFactory,
            new KlarnaInvoiceGatewayFactory(),
            new StripeJsGatewayFactory(),
            new StripeCheckoutGatewayFactory(),
        );
        
        $this->storageFactories = array(
            new DoctrineStorageFactory,
            new FilesystemStorageFactory
        );
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithMinimumConfig()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array()
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithMinimumConfigPlusGateway()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'paypal_express_checkout_nvp' => array(
                            'username' => 'aUsername',
                            'password' => 'aPassword',
                            'signature' => 'aSignature',
                            'sandbox' => true
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithDynamicGateways()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'dynamic_gateways' => array(
                    'config_storage' => array(
                        'Payum\Core\Model\GatewayConfig' => array(
                            'doctrine' => array(
                                'driver' => 'aDriver',
                            )
                        ),
                    ),
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithDynamicGatewaysPlusSonataAdmin()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'dynamic_gateways' => array(
                    'sonata_admin' => true,
                    'config_storage' => array(
                        'Payum\Core\Model\GatewayConfig' => array(
                            'doctrine' => array(
                                'driver' => 'aDriver',
                            )
                        ),
                    ),
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithKlarnaCheckoutGatewayFactory()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'klarna_checkout' => array(
                            'secret' => 'aSecret',
                            'merchant_id' => 'anId',
                            'sandbox' => true
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithDoctrineStorageFactory()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    'stdClass' => array(
                        'doctrine' => array(
                            'driver' => 'aDriver',
                        )
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'omnipay_direct' => array(
                            'type' => 'PayPal_Express',
                            'options' => array(),
                        )
                    )
                )
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithFilesystemStorageFactory()
    {
        $configuration = new MainConfiguration($this->gatewayFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'storages' => array(
                    'stdClass' => array(
                        'filesystem' => array(
                            'storage_dir' => 'a_dir',
                            'id_property' => 'aProp',
                        ),
                    ),
                ),
                'security' => array(
                    'token_storage' => array(
                        'Payum\Core\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'gateways' => array(
                    'a_gateway' => array(
                        'omnipay_offsite' => array(
                            'type' => 'PayPal_Express',
                            'options' => array(),
                        )
                    )
                )
            )
        ));
    }
}