<?php
namespace Payum\Core;

interface PaymentFactoryInterface
{
    /**
     * @param array $config
     *
     * @return PaymentInterface
     */
    function create(array $config = array());
}