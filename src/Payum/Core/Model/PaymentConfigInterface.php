<?php
namespace Payum\Core\Model;

interface PaymentConfigInterface
{
    /**
     * @return string
     */
    public function getPaymentName();

    /**
     * @param string $paymentName
     */
    public function setPaymentName($paymentName);

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