<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

use Payum\Exception\LogicException;

class MainConfiguration implements ConfigurationInterface
{
    /**
     * @var array
     */
    protected $paymentFactories = array();

    /**
     * @var array
     */
    protected $storageFactories = array();

    /**
     * @param array $paymentFactories
     * @param array $storageFactories
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
        
        $this->addTemplateSection($rootNode);
        
        $contextsPrototypeNode = $rootNode
            ->children()
                ->arrayNode('contexts')
                    ->prototype('array')
        ;
        
        $contextsPrototypeNode
            ->children()
                ->scalarNode('status_request_class')->defaultValue('Payum\Request\BinaryMaskStatusRequest')->end()
                ->scalarNode('interactive_controller')->defaultValue('PayumBundle:Capture:interactive')->end()
                ->scalarNode('status_controller')->defaultValue('PayumBundle:Capture:status')->end()
            ->end()
        ;

        $this->addPaymentsSection($contextsPrototypeNode, $this->paymentFactories);
        $this->addStoragesSection($contextsPrototypeNode, $this->storageFactories);

        $contextsPrototypeNode
                    ->validate()
                    ->ifTrue(function($v) {
                        $payments = array();
                        $storages = array();
                        foreach ($v as $name => $value) {
                            if (substr($name, -strlen('_payment')) === '_payment') {
                                $payments[$name] = $value;
                            } else if (substr($name, -strlen('_storage')) === '_storage') {
                                $storages[$name] = $value;
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

                        if (0 == count($storages)) {
                            throw new LogicException(sprintf(
                                'One storage from the %s storages available must be selected',
                                implode(', ', array_keys($storages))
                            ));
                        }
                        if (count($storages) > 1) {
                            throw new LogicException('Only one storage per context could be selected');
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
            
            $factory->addConfiguration($contextsPrototypeNode->children()->arrayNode($paymentName));
        }
    }

    protected function addStoragesSection(ArrayNodeDefinition $contextsPrototypeNode, array $factories)
    {
        foreach ($factories as $factory) {
            $storageNode = $contextsPrototypeNode->children()->arrayNode($factory->getName());

            $storageName = $factory->getName();
            if (empty($storageName)) {
                throw new LogicException('The storage name must not be empty');
            }
            if (substr($storageName, -strlen('_storage')) !== '_storage') {
                throw new LogicException(sprintf(
                    'The storage name must ended with `_storage` but given name is %s',
                    $storageName
                ));
            }

            $factory->addConfiguration($contextsPrototypeNode->children()->arrayNode($storageName));
        }
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addTemplateSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('template')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('engine')->defaultValue('twig')->end()
                ->end()
            ->end()
        ->end();
    }
}