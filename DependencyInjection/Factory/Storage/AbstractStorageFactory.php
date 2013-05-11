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
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, $contextName, $modelClass, $paymentId, array $config)
    {
        $storageId = sprintf(
            'payum.context.%s.storage.%s',
            $contextName,
            strtolower(str_replace('\\', '', $modelClass))
        );
        $storageDefinition = $this->createStorage($container, $contextName, $modelClass, $paymentId, $config);
        
        $container->setDefinition($storageId, $storageDefinition);
        
        if ($config['payment_extension']['enabled']) {
            $this->addStorageExtension($container, $storageId, $paymentId);
        }

        return $storageId;
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder)
    {
        $builder->children()
            ->arrayNode('payment_extension')
                ->addDefaultsIfNotSet()
                ->treatNullLike(array('enabled' => true))
                ->treatTrueLike(array('enabled' => true))
                ->treatFalseLike(array('enabled' => false))
                ->children()
                    ->booleanNode('enabled')->defaultTrue()->end()
                ->end()
            ->end()
            ->end()
        ->end();
    }

    /**
     * @param ContainerBuilder $container
     * @param string $storageId
     * @param string $paymentId
     */
    protected function addStorageExtension(ContainerBuilder $container, $storageId, $paymentId)
    {
        $storageExtensionDefinition = new DefinitionDecorator('payum.extension.storage.prototype');
        $storageExtensionDefinition->replaceArgument(0, new Reference($storageId));
        $storageExtensionDefinition->setPublic(false);
        $storageExtensionId = str_replace('.storage.', '.extension.storage.', $storageId);
        $container->setDefinition($storageExtensionId, $storageExtensionDefinition);

        $paymentDefinition = $container->getDefinition($paymentId);
        $paymentDefinition->addMethodCall('addExtension', array(new Reference($storageExtensionId)));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param array $config
     *
     * @return Definition
     */
    abstract protected function createStorage(ContainerBuilder $container, $contextName, $modelClass, $paymentId, array $config);
}