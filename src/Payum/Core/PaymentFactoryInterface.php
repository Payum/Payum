<?php
namespace Payum\Core;

interface PaymentFactoryInterface
{
    /**
     * @param array $options
     *
     * @return PaymentInterface
     */
    function create(array $options = array());
}