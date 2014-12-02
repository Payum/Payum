<?php
namespace Payum\Core;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Extension\ExtensionInterface;

interface PaymentBuilderInterface
{
    /**
     * @return PaymentInterface
     */
    public function getPayment();

    /**
     * @param string $name
     * @param ActionInterface $action
     *
     * @return self
     */
    function setAction($name, ActionInterface $action);

    /**
     * @param string $name
     * @param object $api
     *
     * @return self
     */
    function setApi($name, $api);

    /**
     * @param string $name
     * @param ExtensionInterface $extension
     *
     * @return self
     */
    function setExtension($name, ExtensionInterface $extension);

    /**
     * @param string $namespace
     * @param string $name
     * @param mixed $value
     *
     * @return self
     */
    function set($namespace, $name, $value);
}