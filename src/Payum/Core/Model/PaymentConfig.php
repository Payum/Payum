<?php
namespace Payum\Core\Model;

class PaymentConfig implements PaymentConfigInterface
{
    /**
     * @var string
     */
    protected $factoryName;

    /**
     * @var string
     */
    protected $paymentName;

    /**
     * @var array
     */
    protected $config;

    public function __construct()
    {
        $this->config = array();
    }

    /**
     * {@inheritDoc}
     */
    public function getFactoryName()
    {
        return $this->factoryName;
    }

    /**
     * {@inheritDoc}
     */
    public function setFactoryName($factoryName)
    {
        $this->factoryName = $factoryName;
    }

    /**
     * @return string
     */
    public function getPaymentName()
    {
        return $this->paymentName;
    }

    /**
     * @param string $paymentName
     */
    public function setPaymentName($paymentName)
    {
        $this->paymentName = $paymentName;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }
}