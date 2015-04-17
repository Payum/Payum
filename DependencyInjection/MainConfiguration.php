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

        $securityNode = $rootNode->children()
            ->arrayNode('security')->isRequired()
        ;
        $this->addSecuritySection($securityNode);

        $dynamicPaymentsNode = $rootNode->children()
            ->arrayNode('dynamic_payments')
        ;
        $this->addDynamicPaymentsSection($dynamicPaymentsNode);
        
        $paymentsPrototypeNode = $rootNode
            ->children()
                ->arrayNode('payments')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->performNoDeepMerging()
        ;

        $this->addPaymentsSection($paymentsPrototypeNode);
        $this->addStoragesSection($rootNode);

        $paymentsPrototypeNode
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
                        throw new LogicException('Only one payment per payment could be selected');
                    }

                    return false;
                })
                ->thenInvalid('A message')
            ->end()
        ->end();
        
        return $tb;
    }

    /**
     * @param ArrayNodeDefinition $paymentsPrototypeNode
     */
    protected function addPaymentsSection(ArrayNodeDefinition $paymentsPrototypeNode)
    {
        foreach ($this->paymentFactories as $factory) {
            $factory->addConfiguration(
                $paymentsPrototypeNode->children()->arrayNode($factory->getName())
            );
        }
    }

    /**
     * @param ArrayNodeDefinition $rootPrototypeNode
     */
    protected function addStoragesSection(ArrayNodeDefinition $rootPrototypeNode)
    {
        $storageNode = $rootPrototypeNode->children()
                ->arrayNode('storages')
                ->validate()
                    ->ifTrue(function($v) {
                        $storages = $v;
                        unset($storages['extension']);

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
                    unset($storages['extension']);

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
            ->arrayNode('extension')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('all')->defaultValue(true)->end()
                    ->arrayNode('payments')
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
                    if (false == $rc->implementsInterface('Payum\Core\Security\TokenInterface')) {
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

    /**
     * @param ArrayNodeDefinition $dynamicPaymentsNode
     */
    protected function addDynamicPaymentsSection(ArrayNodeDefinition $dynamicPaymentsNode)
    {
        $dynamicPaymentsNode->children()
            ->booleanNode('sonata_admin')->defaultFalse()
        ;

        $storageNode = $dynamicPaymentsNode->children()
            ->arrayNode('config_storage')
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
                    if (false == $rc->implementsInterface('Payum\Core\Model\PaymentConfigInterface')) {
                        throw new LogicException('The config class must implement `Payum\Core\Model\PaymentConfigInterface` interface');
                    }

                    if (count($v) > 1) {
                        throw new LogicException('Only one config storage could be configured.');
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