<?php

namespace Payum\Core\Storage;

use Payum\Core\Model\GatewayConfigInterface;

final class EncryptingGatewayConfigurationStorage implements StorageInterface
{
    /**
     * @var StorageInterface
     */
    private $defaultStorage;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $initializationVector;

    /**
     * @param StorageInterface $defaultStorage
     * @param string $algorithm
     * @param string $secret
     * @param string $initializationVector
     */
    public function __construct(StorageInterface $defaultStorage, $algorithm, $secret, $initializationVector)
    {
        $this->defaultStorage = $defaultStorage;
        $this->algorithm = $algorithm;
        $this->secret = $secret;
        $this->initializationVector = $initializationVector;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        return $this->defaultStorage->create();
    }

    /**
     * {@inheritdoc}
     */
    public function support($model)
    {
        return $this->defaultStorage->support($model);
    }

    /**
     * {@inheritdoc}
     */
    public function update($model)
    {
        $this->defaultStorage->update($model);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($model)
    {
        $this->defaultStorage->delete($model);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        $model = $this->defaultStorage->find($id);
        $this->decryptGatewayConfiguration($model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria)
    {
        $model = $this->defaultStorage->findBy($criteria);
        $this->decryptGatewayConfiguration($model);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function identify($model)
    {
        return $this->defaultStorage->identify($model);
    }

    /**
     * @param object $model
     */
    private function decryptGatewayConfiguration($model)
    {
        if (is_array($model)) {
            $model = end($model);
        }

        if ($model instanceof GatewayConfigInterface) {
            $model->setConfig(
                array_map(function ($encryptedConfigurationValue) {
                    $decryptedConfigurationValue = openssl_decrypt(
                        base64_decode($encryptedConfigurationValue),
                        $this->algorithm,
                        $this->secret,
                        OPENSSL_RAW_DATA,
                        $this->initializationVector
                    );

                    if (false === $decryptedConfigurationValue) {
                        throw new \RuntimeException('Gateway configuration decrypting failed.');
                    }

                    return trim($decryptedConfigurationValue);
                }, $model->getConfig())
            );
        }
    }
}
