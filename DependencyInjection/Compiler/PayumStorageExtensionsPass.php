<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Compiler;

use Payum\Core\Exception\LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class PayumStorageExtensionsPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('payum.storage_extension') as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $storageExtension = new DefinitionDecorator('payum.extension.storage.prototype');
                $storageExtension->replaceArgument(0, new Reference($id));
                $storageExtension->setPublic(true);

                if (false !== strpos($id, '.storage.')) {
                    $storageExtensionId = str_replace('.storage.', '.extension.storage.', $id);
                } else {
                    throw new LogicException(sprintf('In order to add storage to extension the storage %id has to contains ".storage." inside.', $id));
                }

                $container->setDefinition($storageExtensionId, $storageExtension);
                $storageExtension->addTag('payum.extension', $attributes);
            }
        }
    }
}