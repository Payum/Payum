<?php
namespace Payum\Core;

interface PaymentFactoryInterface
{
    /**
     * @param array $config
     *
     * @return array
     */
    function createConfig(array $config = array());

    /**
     * @param array $config
     *
     * @return PaymentInterface
     */
    function create(array $config = array());
}