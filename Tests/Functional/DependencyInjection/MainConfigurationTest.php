<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillDirectPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillOffsitePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaCheckoutPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\KlarnaInvoicePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayDirectPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayOffsitePaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\StripeCheckoutPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\StripeJsPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\DoctrineStorageFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\FilesystemStorageFactory;

class MainConfigurationTest extends  \PHPUnit_Framework_TestCase
{
    protected $paymentFactories = array();

    protected $storageFactories = array();
    
    protected function setUp()
    {
        $this->paymentFactories = array(
            new PaypalExpressCheckoutNvpPaymentFactory,
            new PaypalProCheckoutNvpPaymentFactory,
            new AuthorizeNetAimPaymentFactory,
            new Be2BillDirectPaymentFactory,
            new Be2BillOffsitePaymentFactory(),
            new OmnipayDirectPaymentFactory,
            new OmnipayOffsitePaymentFactory(),
            new KlarnaCheckoutPaymentFactory,
            new KlarnaInvoicePaymentFactory(),
            new StripeJsPaymentFactory(),
            new StripeCheckoutPaymentFactory(),
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
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

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
                'payments' => array()
            )
        ));
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithMinimumConfigPlusPayment()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

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
                'payments' => array(
                    'a_payment' => array(
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
    public function shouldPassConfigurationProcessingWithDynamicPayments()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

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
                'dynamic_payments' => array(
                    'config_storage' => array(
                        'Payum\Core\Model\PaymentConfig' => array(
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
    public function shouldPassConfigurationProcessingWithDynamicPaymentsPlusSonataAdmin()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

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
                'dynamic_payments' => array(
                    'sonata_admin' => true,
                    'config_storage' => array(
                        'Payum\Core\Model\PaymentConfig' => array(
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
    public function shouldPassConfigurationProcessingWithKlarnaCheckoutPaymentFactory()
    {
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

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
                'payments' => array(
                    'a_payment' => array(
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
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

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
                'payments' => array(
                    'a_payment' => array(
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
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

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
                'payments' => array(
                    'a_payment' => array(
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