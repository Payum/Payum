<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractStorageFactory implements StorageFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(ContainerBuilder $container, $modelClass, array $config)
    {
        $storageId = sprintf('payum.storage.%s', strtolower(str_replace(array('\\\\', '\\'), '_', $modelClass)));

        $storageDefinition = $this->createStorage($container, $modelClass, $config);
        
        $container->setDefinition($storageId, $storageDefinition);

        return $storageId;
    }

    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
    }

    /**
     * @param ContainerBuilder $container
     * @param string $modelClass
     * @param array $config
     *
     * @return Definition
     */
    abstract protected function createStorage(ContainerBuilder $container, $modelClass, array $config);
}