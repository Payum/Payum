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
        foreach ($paymentFactories as $paymentFactory) {
            $this->paymentFactories[$paymentFactory->getName()] = $paymentFactory;
        }

        foreach ($storageFactories as $storageFactory) {
            $this->storageFactories[$storageFactory->getName()] = $storageFactory;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $paymentFactories = $this->paymentFactories;
        
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
                    ->ifTrue(function($v) use($paymentFactories) {
                        $selectedPayments = array();
                        foreach ($v as $name => $value) {
                            if (isset($this->paymentFactories[$name])) {
                                $selectedPayments[$name] = $paymentFactories[$name];
                            }
                        }
                
                        if (0 == count($selectedPayments)) {
                            throw new LogicException(sprintf(
                                'One payment from the %s payments available must be selected',
                                implode(', ', array_keys($selectedPayments))
                            ));
                        }
                        if (count($selectedPayments) > 1) {
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
            $factory->addConfiguration(
                $contextsPrototypeNode->children()->arrayNode($factory->getName())
            );
        }
    }

    protected function addStoragesSection(ArrayNodeDefinition $contextsPrototypeNode, array $factories)
    {
        $storageNode = $contextsPrototypeNode->children()
                ->arrayNode('storages')
                ->validate()
                    ->ifTrue(function($v) {
                        foreach($v as $key => $value) {
                            if (false == class_exists($key)) {
                                throw new LogicException(sprintf(
                                    'The storage entry must be a valid model class. It is set %s',
                                    $key
                                ));
                            }
                        }
                    
                        return false;
                    })
                    ->thenInvalid('A message')
                ->end()
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
            $factory->addConfiguration(
                $storageNode->children()->arrayNode($factory->getName())
            );
        }
    }
}