<?php
namespace Payum\Core\Model;

interface PaymentConfigInterface
{
    /**
     * @return string
     */
    function getFactoryName();

    /**
     * @param string $name
     */
    function setFactoryName($name);

    /**
     * @param array $config
     */
    function setConfig(array $config);

    /**
     * @return array
     */
    function getConfig();
}