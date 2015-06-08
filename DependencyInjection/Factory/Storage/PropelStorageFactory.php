<?php

namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;


class PropelStorageFactory  extends AbstractStorageFactory{
    
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return "propel";
    }
    
    /**
     * {@inheritDoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        parent::addConfiguration($builder);
        $builder
            ->beforeNormalization()->ifString()->then(function($v) {
                
                return array('v' => $v);
            })->end()
            ->children()
                ->scalarNode('driver')->isRequired()->cannotBeEmpty()->end()
            ->end();        
    }
    
    /**
     * {@inheritDoc}
     */
    protected function createStorage(ContainerBuilder $container, $modelClass, array $config)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../../Resources/config/storage'));
        $loader->load('propel.'.$config['driver'].'.xml');
        
        $storage = new DefinitionDecorator('payum.storage.propel.%s', $config['driver']);
        $storage->setPublic(true);
        $storage->replaceArgument(1, $modelClass);
        
        return $storage;
    }
}
