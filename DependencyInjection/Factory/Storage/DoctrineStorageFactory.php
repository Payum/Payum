<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class DoctrineStorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'doctrine';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        
        $builder->children()
            ->scalarNode('driver')->isRequired()->cannotBeEmpty()->end()
        ->end();
    }

    /**
     * {@inheritdoc}
     */
    protected function createStorage(ContainerBuilder $container, $contextName, $modelClass, $paymentId, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
        $loader->load('doctrine.'.$config['driver'].'.xml');

        $contextStorageDefinition = new DefinitionDecorator('payum.storage.doctrine.'.$config['driver']);
        $contextStorageDefinition->setPublic(true);
        $contextStorageDefinition->replaceArgument(1, $modelClass);
        
        return $contextStorageDefinition;
    }
}