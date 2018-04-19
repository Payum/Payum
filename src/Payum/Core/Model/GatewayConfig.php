<?php
namespace Payum\Core\Model;

use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

class GatewayConfig implements GatewayConfigInterface, CryptedInterface
{
    /**
     * @var string
     */
    protected $factoryName;

    /**
     * @var string
     */
    protected $gatewayName;

    /**
     * @var array
     */
    protected $config;

    /**
     * Note: This should not be persisted to database
     *
     * @var array
     */
    protected $decryptedConfig;

    public function __construct()
    {
        $this->config = [];
        $this->decryptedConfig = [];
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
    public function getGatewayName()
    {
        return $this->gatewayName;
    }

    /**
     * @param string $gatewayName
     */
    public function setGatewayName($gatewayName)
    {
        $this->gatewayName = $gatewayName;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfig()
    {
        if (isset($this->config['encrypted'])) {
            return $this->decryptedConfig;
        }

        return $this->config;
    }

    /**
     * {@inheritDoc}
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
        $this->decryptedConfig = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(CypherInterface $cypher)
    {
        if (empty($this->config['encrypted'])) {
            return;
        }

        foreach ($this->config as $name => $value) {
            if ('encrypted' == $name || is_bool($value)) {
                $this->decryptedConfig[$name] = $value;

                continue;
            }

            $this->decryptedConfig[$name] = $cypher->decrypt($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(CypherInterface $cypher)
    {
        $this->decryptedConfig['encrypted'] = true;

        foreach ($this->decryptedConfig as $name => $value) {
            if ('encrypted' == $name || is_bool($value)) {
                $this->config[$name] = $value;

                continue;
            }

            $this->config[$name] = $cypher->encrypt($value);
        }
    }
}
