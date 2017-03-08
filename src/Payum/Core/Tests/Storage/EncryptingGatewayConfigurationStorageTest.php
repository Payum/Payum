<?php

namespace Payum\Core\Tests\Storage;

use Payum\Core\Model\CreditCardInterface;
use Payum\Core\Model\GatewayConfig;
use Payum\Core\Storage\EncryptingGatewayConfigurationStorage;
use Payum\Core\Storage\StorageInterface;

final class EncryptingGatewayConfigurationStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itDecryptsGatewayConfigurationWhileFetchingModelObjectById()
    {
        $initializationVector = openssl_random_pseudo_bytes(16);
        $encryptedConfiguration = [
            'apiKey' => base64_encode(openssl_encrypt(
                'secretKey',
                'AES-128-CBC',
                'secret',
                OPENSSL_RAW_DATA,
                $initializationVector
            ))
        ];

        $model = new GatewayConfig();
        $model->setConfig($encryptedConfiguration);

        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->find('id')->willReturn($model);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'secret',
            $initializationVector
        );

        $model = $storage->find('id');
        $this->assertEquals('secretKey', $model->getConfig()['apiKey']);
    }

    /**
     * @test
     */
    public function itDecryptsGatewayConfigurationWhileFetchingModelObjectByCriteria()
    {
        $initializationVector = openssl_random_pseudo_bytes(16);
        $encryptedConfiguration = [
            'apiKey' => base64_encode(openssl_encrypt(
                'secretKey',
                'AES-128-CBC',
                'secret',
                OPENSSL_RAW_DATA,
                $initializationVector
            ))
        ];

        $model = new GatewayConfig();
        $model->setConfig($encryptedConfiguration);

        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->findBy(['gatewayName' => 'paypal'])->willReturn($model);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'secret',
            $initializationVector
        );

        $model = $storage->findBy(['gatewayName' => 'paypal']);
        $this->assertEquals('secretKey', $model->getConfig()['apiKey']);
    }

    /**
     * @test
     */
    public function itDoesNotDecryptGatewayConfigurationIfFetchedModelByIdIsNotGatewayConfiguration()
    {
        $expectedModel = $this->prophesize(CreditCardInterface::class);
        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->find('id')->willReturn($expectedModel);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'secret',
            openssl_random_pseudo_bytes(16)
        );

        $model = $storage->find('id');

        $this->assertEquals($expectedModel->reveal(), $model);
    }

    /**
     * @test
     */
    public function itDoesNotDecryptGatewayConfigurationIfFetchedModelByCriteriaIsNotGatewayConfiguration()
    {
        $expectedModel = $this->prophesize(CreditCardInterface::class);
        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->findBy(['someProperty' => 'somePropertyValue'])->willReturn($expectedModel);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'secret',
            openssl_random_pseudo_bytes(16)
        );

        $model = $storage->findBy(['someProperty' => 'somePropertyValue']);

        $this->assertEquals($expectedModel->reveal(), $model);
    }

    /**
     * @test
     */
    public function itCouldFailIfCannotDecryptConfigurationValueWithWrongInitializationVectorWhileFetchingById()
    {
        $this->setExpectedException(\RuntimeException::class);
        $encryptedConfiguration = [
            'apiKey' => openssl_encrypt(
                'secretKey',
                'AES-128-CBC',
                'secret',
                OPENSSL_RAW_DATA,
                openssl_random_pseudo_bytes(16)
            )
        ];

        $model = new GatewayConfig();
        $model->setConfig($encryptedConfiguration);

        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->findBy(['gatewayName' => 'paypal'])->willReturn($model);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'secret',
            openssl_random_pseudo_bytes(16)
        );

        $storage->findBy(['gatewayName' => 'paypal']);
    }

    /**
     * @test
     */
    public function itCouldFailIfCannotDecryptConfigurationValueWithWrongInitializationVectorWhileFetchingByCriteria()
    {
        $this->setExpectedException(\RuntimeException::class);
        $encryptedConfiguration = [
            'apiKey' => openssl_encrypt(
                'secretKey',
                'AES-128-CBC',
                'secret',
                OPENSSL_RAW_DATA,
                openssl_random_pseudo_bytes(16)
            )
        ];

        $model = new GatewayConfig();
        $model->setConfig($encryptedConfiguration);

        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->find('id')->willReturn($model);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'secret',
            openssl_random_pseudo_bytes(16)
        );

        $storage->find('id');
    }

    /**
     * @test
     */
    public function itCouldFailIfCannotDecryptConfigurationValueWithWrongEncryptingMethodWhileFetchingById()
    {
        $initializationVector = openssl_random_pseudo_bytes(16);
        $this->setExpectedException(\RuntimeException::class);
        $encryptedConfiguration = [
            'apiKey' => openssl_encrypt(
                'secretKey',
                'AES-128-CFB',
                'secret',
                OPENSSL_RAW_DATA,
                $initializationVector
            )
        ];

        $model = new GatewayConfig();
        $model->setConfig($encryptedConfiguration);

        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->findBy(['gatewayName' => 'paypal'])->willReturn($model);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'secret',
            $initializationVector
        );

        $storage->findBy(['gatewayName' => 'paypal']);
    }

    /**
     * @test
     */
    public function itCouldFailIfCannotDecryptConfigurationValueWithWrongEncryptingMethodWhileFetchingByCriteria()
    {
        $initializationVector = openssl_random_pseudo_bytes(16);
        $this->setExpectedException(\RuntimeException::class);
        $encryptedConfiguration = [
            'apiKey' => openssl_encrypt(
                'secretKey',
                'AES-128-CFB',
                'secret',
                OPENSSL_RAW_DATA,
                $initializationVector
            )
        ];

        $model = new GatewayConfig();
        $model->setConfig($encryptedConfiguration);

        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->find('id')->willReturn($model);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'secret',
            $initializationVector
        );

        $storage->find('id');
    }

    /**
     * @test
     */
    public function itCouldFailIfCannotDecryptConfigurationValueWithWrongSecretWhileFetchingById()
    {
        $initializationVector = openssl_random_pseudo_bytes(16);
        $this->setExpectedException(\RuntimeException::class);
        $encryptedConfiguration = [
            'apiKey' => openssl_encrypt(
                'secretKey',
                'AES-128-CBC',
                'secret',
                OPENSSL_RAW_DATA,
                $initializationVector
            )
        ];

        $model = new GatewayConfig();
        $model->setConfig($encryptedConfiguration);

        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->findBy(['gatewayName' => 'paypal'])->willReturn($model);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'wrongSecret',
            $initializationVector
        );

        $storage->findBy(['gatewayName' => 'paypal']);
    }

    /**
     * @test
     */
    public function itCouldFailIfCannotDecryptConfigurationValueWithWrongSecretWhileFetchingByCriteria()
    {
        $initializationVector = openssl_random_pseudo_bytes(16);
        $this->setExpectedException(\RuntimeException::class);
        $encryptedConfiguration = [
            'apiKey' => openssl_encrypt(
                'secretKey',
                'AES-128-CBC',
                'secret',
                OPENSSL_RAW_DATA,
                $initializationVector
            )
        ];

        $model = new GatewayConfig();
        $model->setConfig($encryptedConfiguration);

        $decoratedStorage = $this->prophesize(StorageInterface::class);
        $decoratedStorage->find('id')->willReturn($model);

        $storage = new EncryptingGatewayConfigurationStorage(
            $decoratedStorage->reveal(),
            'AES-128-CBC',
            'wrongSecret',
            $initializationVector
        );

        $storage->find('id');
    }
}
