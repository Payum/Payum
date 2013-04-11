<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;

use Payum\Exception\LogicException;

class MainConfiguration implements ConfigurationInterface
{
    /**
     * @var PaymentFactoryInterface[]
     */
    protected $paymentFactories = array();

    /**
     * @var StorageFactoryInterface[]
     */
    protected $storageFactories = array();

    /**
     * @param PaymentFactoryInterface[] $paymentFactories
     * @param StorageFactoryInterface[] $storageFactories
     */
    public function __construct(array $paymentFactories, array $storageFactories)
    {
        $this->paymentFactories = $paymentFactories;
        $this->storageFactories = $storageFactories;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $rootNode = $tb->root('payum');
        
        $contextsPrototypeNode = $rootNode
            ->children()
                ->arrayNode('contexts')
                    ->prototype('array')
        ;

        $this->addPaymentsSection($contextsPrototypeNode, $this->paymentFactories);
        $this->addStoragesSection($contextsPrototypeNode, $this->storageFactories);

        $contextsPrototypeNode
                    ->validate()
                    ->ifTrue(function($v) {
                        $payments = array();
                        foreach ($v as $name => $value) {
                            if (substr($name, -strlen('_payment')) === '_payment') {
                                $payments[$name] = $value;
                            }
                        }
                
                        if (0 == count($payments)) {
                            throw new LogicException(sprintf(
                                'One payment from the %s payments available must be selected',
                                implode(', ', array_keys($payments))
                            ));
                        }
                        if (count($payments) > 1) {
                            throw new LogicException('Only one payment per context could be selected');
                        }
                
                        return false;
                    })
                    ->thenInvalid('A message')
                ->end()
            ->end()
        ;
        
        return $tb;
    }
    
    protected function addPaymentsSection(ArrayNodeDefinition $contextsPrototypeNode, array $factories)
    {
        foreach ($factories as $factory) {
            $paymentName = $factory->getName();
            if (empty($paymentName)) {
                throw new LogicException('The payment name must not be empty');
            }
            if (substr($paymentName, -strlen('_payment')) !== '_payment') {
                throw new LogicException(sprintf(
                    'The payment name must ended with `_payment` but given name is %s',
                    $paymentName
                ));
            }

            $paymentSection = $contextsPrototypeNode->children()->arrayNode($paymentName);
            
            $factory->addConfiguration($paymentSection);
        }
    }

    protected function addStoragesSection(ArrayNodeDefinition $contextsPrototypeNode, array $factories)
    {
        $storageNode = $contextsPrototypeNode->children()
            ->arrayNode('storages')
                ->useAttributeAsKey('key')
                ->prototype('array')
        ;

        $storageNode
            ->validate()
                ->ifTrue(function($v) {
                    if (count($v) == 0) {
                        throw new LogicException('At least one storage must be configured.');
                    }
                    if (count($v) > 1) {
                        throw new LogicException('Only one storage per entry could be selected');
                    }
                    
                    return false;
                })
                ->thenInvalid('A message')
            ->end()
        ;
        
        foreach ($factories as $factory) {
            $storageName = $factory->getName();
            if (empty($storageName)) {
                throw new LogicException('The storage name must not be empty');
            }
            
            $factory->addConfiguration($storageNode->children()->arrayNode($storageName));
        }
    }
}
