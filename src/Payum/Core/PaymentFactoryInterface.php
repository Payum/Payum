<?php
namespace Payum\Core;

interface PaymentFactoryInterface
{
    /**
     * @param array $options
     *
     * @return PaymentInterface
     */
    static function create(array $options = array());

    /**
     * @param array $options
     *
     * @return PaymentBuilderInterface
     */
    static function createBuilder(array $options = array());
}