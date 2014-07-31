<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

class CustomStorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'custom';
    }

    /**
     * {@inheritdoc}
     */
    protected function createStorage(ContainerBuilder $container, $modelClass, array $config)
    {
        return new DefinitionDecorator($config['service']);
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);

        $builder->children()
            ->scalarNode('service')->isRequired()->cannotBeEmpty()->end()
        ->end();
    }
}
