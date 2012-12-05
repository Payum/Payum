<?php
namespace Payum\PaymentBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class FilesystemStorageFactory implements StorageFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        $contextStorageDefinition = new DefinitionDecorator('payum_payment.storage.filesystem.default');
        $contextStorageDefinition->replaceArgument(0, $config['storage_dir']);
        $contextStorageDefinition->replaceArgument(1, $config['model_class']);
        $contextStorageDefinition->replaceArgument(2, $config['id_property']);
        
        $contextStorageId = 'payum_payment.context.'.$contextName.'.storage';
        $container->setDefinition($contextStorageId, $contextStorageDefinition);
        
        return $contextStorageId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filesystem_storage';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder->children()
            ->scalarNode('storage_dir')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('id_property')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('model_class')->isRequired()->cannotBeEmpty()->end()
        ->end();
    }
    
    protected function load(ContainerBuilder $container)
    {
        static $loaded;
        
        if (false == $loaded) {
            // load services
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
            $loader->load('filesystem.xml');
            
            $loaded = true;
        }
    }
}