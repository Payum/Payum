<?php
namespace Payum\Core\Model;

use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

class GatewayConfig implements GatewayConfigInterface, CryptedInterface
{
    protected string $factoryName;

    protected string $gatewayName;

    protected array $config;

    /**
     * Note: This should not be persisted to database
     *
     * @var array
     */
    protected array $decryptedConfig;

    public function __construct()
    {
        $this->config = [];
        $this->decryptedConfig = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getFactoryName(): string
    {
        return $this->factoryName;
    }

    /**
     * {@inheritDoc}
     */
    public function setFactoryName($factoryName): void
    {
        $this->factoryName = $factoryName;
    }

    public function getGatewayName(): string
    {
        return $this->gatewayName;
    }
    
    public function setGatewayName(string $gatewayName): void
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
