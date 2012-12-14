<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class DoctrineStorageFactory implements StorageFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, array $config)
    {
        $this->load($container, $config['driver']);
        
        $contextStorageDefinition = new DefinitionDecorator('payum.storage.doctrine.'.$config['driver']);
        $contextStorageDefinition->setPublic(true);
        $contextStorageDefinition->replaceArgument(1, $config['model_class']);
        
        $contextStorageId = 'payum.context.'.$contextName.'.storage';
        $container->setDefinition($contextStorageId, $contextStorageDefinition);
        
        return $contextStorageId;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'doctrine_storage';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder->children()
            ->scalarNode('driver')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('model_class')->isRequired()->cannotBeEmpty()->end()
        ->end();
    }
    
    protected function load(ContainerBuilder $container, $dbDriver)
    {
        static $loaded;
        
        if (false == $loaded) {
            $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
            $loader->load('doctrine.'.$dbDriver.'.xml');
            
            $loaded = true;
        }
    }
}