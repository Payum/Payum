<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class FilesystemStorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filesystem';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('storage_dir')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('id_property')->defaultValue(null)->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function createStorage(ContainerBuilder $container, $modelClass, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
        $loader->load('filesystem.xml');

        $storage = new DefinitionDecorator('payum.storage.filesystem.prototype');
        $storage->setPublic(true);
        $storage->replaceArgument(0, $config['storage_dir']);
        $storage->replaceArgument(1, $modelClass);
        $storage->replaceArgument(2, $config['id_property']);

        return $storage;
    }
}