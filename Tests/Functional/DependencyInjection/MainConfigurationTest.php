<?php
namespace Payum\Bundle\PayumBundle\Tests\Functional\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\AuthorizeNetAimPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\Be2BillPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\OmnipayPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalProCheckoutNvpPaymentFactory;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Payum\Bundle\PayumBundle\DependencyInjection\MainConfiguration;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaypalExpressCheckoutNvpPaymentFactory;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
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
            new Be2BillPaymentFactory,
            new OmnipayPaymentFactory
        );
        
        $this->storageFactories = array(
            new DoctrineStorageFactory,
            new FilesystemStorageFactory
        );
    }

    /**
     * @test
     */
    public function shouldPassConfigurationProcessingWithPaypalExpressCheckoutNvpPaymentFactory()
    {
        if (false == class_exists('Payum\Paypal\ExpressCheckout\Nvp\PaymentFactory')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }
        
        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'paypal_express_checkout_nvp' => array(
                            'api' => array(
                                'options' => array(
                                    'username' => 'aUsername',
                                    'password' => 'aPassword',
                                    'signature' => 'aSignature',
                                    'sandbox' => true
                                )
                            )
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
        if (false == class_exists('Doctrine\ORM\Configuration')) {
            $this->markTestSkipped('Skipped because payment library is not installed.');
        }

        $configuration = new MainConfiguration($this->paymentFactories, $this->storageFactories);

        $processor = new Processor();

        $processor->processConfiguration($configuration, array(
            'payum' => array(
                'security' => array(
                    'token_storage' => array(
                        'Payum\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'storages' => array(
                            'stdClass' => array(
                                'doctrine' => array(
                                    'driver' => 'aDriver',
                                ) 
                            ),
                        ),
                        'omnipay' => array(
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
                'security' => array(
                    'token_storage' => array(
                        'Payum\Model\Token' => array(
                            'filesystem' => array(
                                'storage_dir' => sys_get_temp_dir(),
                                'id_property' => 'hash'
                            )
                        )
                    )
                ),
                'contexts' => array(
                    'a_context' => array(
                        'storages' => array(
                            'stdClass' => array(
                                'filesystem' => array(
                                    'storage_dir' => 'a_dir',
                                    'id_property' => 'aProp',
                                ),
                            ),
                        ),
                        'omnipay' => array(
                            'type' => 'PayPal_Express',
                            'options' => array(),
                        )
                    )
                )
            )
        ));
    }
}