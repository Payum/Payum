<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class NullStorageFactory implements StorageFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        $contextStorageId = 'payum.context.'.$contextName.'.storage';
        $container->setDefinition($contextStorageId, new DefinitionDecorator('payum.storage.null'));
        
        return $contextStorageId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'null_storage';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
    }
}