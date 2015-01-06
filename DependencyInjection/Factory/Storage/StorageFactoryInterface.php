<?php
namespace Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

interface StorageFactoryInterface
{
    /**
     * @param ContainerBuilder $container
     * @param string $modelClass
     * @param array $config
     *
     * @return string The payment serviceId
     */
    function create(ContainerBuilder $container, $modelClass, array $config);

    /**
     * The storage name, 
     * For example filesystem, doctrine, propel etc.
     * 
     * @return string
     */
    function getName();

    /**
     * @param ArrayNodeDefinition $builder
     * 
     * @return void
     */
    function addConfiguration(ArrayNodeDefinition $builder);
}