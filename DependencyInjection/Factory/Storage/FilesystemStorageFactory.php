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
            ->scalarNode('id_property')->isRequired()->cannotBeEmpty()->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function createStorage(ContainerBuilder $container, $contextName, $modelClass, $paymentId, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
        $loader->load('filesystem.xml');

        $contextStorageDefinition = new DefinitionDecorator('payum.storage.filesystem.prototype');
        $contextStorageDefinition->setPublic(true);
        $contextStorageDefinition->replaceArgument(0, $config['storage_dir']);
        $contextStorageDefinition->replaceArgument(1, $modelClass);
        $contextStorageDefinition->replaceArgument(2, $config['id_property']);

        return $contextStorageDefinition;
    }
}