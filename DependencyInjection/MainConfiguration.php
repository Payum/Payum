<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\StorageFactoryInterface;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Payment\PaymentFactoryInterface;
use Payum\Core\Exception\LogicException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $paymentFactories = $this->paymentFactories;
        
        $tb = new TreeBuilder();
        $rootNode = $tb->root('payum');

        $securityNode = $rootNode
            ->children()
                ->arrayNode('security')
                    ->isRequired()
        ;

        $this->addSecuritySection($securityNode);
        
        $contextsPrototypeNode = $rootNode
            ->children()
                ->arrayNode('contexts')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
        ;

        $this->addPaymentsSection($contextsPrototypeNode, $this->paymentFactories);
        $this->addStoragesSection($rootNode, $this->storageFactories);

        $contextsPrototypeNode
                    ->validate()
                    ->ifTrue(function($v) use($paymentFactories) {
                        $selectedPayments = array();
                        foreach ($v as $name => $value) {
                            if (isset($paymentFactories[$name])) {
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

    /**
     * @param ArrayNodeDefinition $contextsPrototypeNode
     */
    protected function addPaymentsSection(ArrayNodeDefinition $contextsPrototypeNode)
    {
        foreach ($this->paymentFactories as $factory) {
            $factory->addConfiguration(
                $contextsPrototypeNode->children()->arrayNode($factory->getName())
            );
        }
    }

    /**
     * @param ArrayNodeDefinition $contextsPrototypeNode
     */
    protected function addStoragesSection(ArrayNodeDefinition $contextsPrototypeNode)
    {
        $storageNode = $contextsPrototypeNode->children()
                ->arrayNode('storages')
                ->validate()
                    ->ifTrue(function($v) {
                        $storages = $v;
                        unset($storages['payment']);

                        foreach($storages as $key => $value) {
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
                    $storages = $v;
                    unset($storages['payment']);

                    if (count($storages) == 0) {
                        throw new LogicException('At least one storage must be configured.');
                    }
                    if (count($storages) > 1) {
                        throw new LogicException('Only one storage per entry could be selected');
                    }
                    
                    return false;
                })
                ->thenInvalid('A message')
            ->end()
        ;

        $storageNode->children()
            ->arrayNode('payment')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('all')->defaultValue(true)->end()
                    ->arrayNode('contexts')
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')
                    ->end()->end()
                    ->arrayNode('factories')
                        ->useAttributeAsKey('key')
                        ->prototype('scalar')
                    ->end()->end()
                ->end()
            ->end()
        ->end();
        
        foreach ($this->storageFactories as $factory) {
            $factory->addConfiguration(
                $storageNode->children()->arrayNode($factory->getName())
            );
        }
    }

    /**
     * @param ArrayNodeDefinition $securityNode
     */
    protected function addSecuritySection(ArrayNodeDefinition $securityNode)
    {
        $storageNode = $securityNode->children()
            ->arrayNode('token_storage')
            ->isRequired()
            ->validate()
            ->ifTrue(function($v) {
                foreach($v as $key => $value) {
                    if (false == class_exists($key)) {
                        throw new LogicException(sprintf(
                            'The storage entry must be a valid model class. It is set %s',
                            $key
                        ));
                    }

                    $rc = new \ReflectionClass($key);
                    if (false == $rc->isSubclassOf('Payum\Core\Security\TokenInterface')) {
                        throw new LogicException('The token class must implement `Payum\Core\Security\TokenInterface` interface');
                    }

                    if (count($v) > 1) {
                        throw new LogicException('Only one token storage could be configured.');
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

        foreach ($this->storageFactories as $factory) {
            $factory->addConfiguration(
                $storageNode->children()->arrayNode($factory->getName())
            );
        }
    }
}