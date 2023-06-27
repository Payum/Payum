<?php

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\GatewayConfig;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GatewayConfigTest extends TestCase
{
    public function testShouldExtendDetailsAwareInterface(): void
    {
        $rc = new ReflectionClass(GatewayConfig::class);

        $this->assertTrue($rc->implementsInterface(GatewayConfigInterface::class));
    }

    public function testShouldImplementCryptedInterface(): void
    {
        $rc = new ReflectionClass(GatewayConfig::class);

        $this->assertTrue($rc->implementsInterface(CryptedInterface::class));
    }

    public function testShouldAllowGetPreviouslySetFactoryName(): void
    {
        $config = new GatewayConfig();

        $config->setFactoryName('theName');

        $this->assertSame('theName', $config->getFactoryName());
    }

    public function testShouldAllowGetPreviouslySetGatewayName(): void
    {
        $config = new GatewayConfig();

        $config->setGatewayName('theName');

        $this->assertSame('theName', $config->getGatewayName());
    }

    public function testShouldAllowGetDefaultConfigSetInConstructor(): void
    {
        $config = new GatewayConfig();

        $this->assertEquals([], $config->getConfig());
    }

    public function testShouldAllowGetPreviouslySetConfig(): void
    {
        $config = new GatewayConfig();

        $config->setConfig([
            'foo' => 'fooVal',
        ]);

        $this->assertEquals([
            'foo' => 'fooVal',
        ], $config->getConfig());
    }

    public function testShouldDecryptConfigValuesOnDecrypt(): void
    {
        $encryptedConfig = [
            'encrypted' => true,
            'foo' => 'encryptedFooVal',
            'bar' => 'encryptedBarVal',
        ];
        $expectedDecryptedConfig = [
            'encrypted' => true,
            'foo' => 'decrypted-encryptedFooVal',
            'bar' => 'decrypted-encryptedBarVal',
        ];

        $config = new GatewayConfig();
        $config->setConfig($encryptedConfig);

        $this->assertSame($encryptedConfig, $config->getConfig());

        $config->decrypt($this->createDummyCypher());

        $this->assertSame($expectedDecryptedConfig, $config->getConfig());
    }

    public function testShouldDoNothingOnDecryptIfConfigIsNotEncrypted(): void
    {
        $plainConfig = [
            'encrypted' => false,
            'foo' => 'encryptedFooVal',
            'bar' => 'encryptedBarVal',
        ];

        $config = new GatewayConfig();
        $config->setConfig($plainConfig);

        $this->assertSame($plainConfig, $config->getConfig());

        $config->decrypt($this->createDummyCypher());

        $this->assertSame($plainConfig, $config->getConfig());
    }

    public function testShouldEncryptConfigValuesOnEncrypt(): void
    {
        $plainConfig = [
            'foo' => 'plainFooVal',
            'bar' => 'plainBarVal',
        ];

        $expectedDecryptedConfig = [
            'foo' => 'plainFooVal',
            'bar' => 'plainBarVal',
            'encrypted' => true,
        ];

        $config = new GatewayConfig();
        $config->setConfig($plainConfig);

        $this->assertSame($plainConfig, $config->getConfig());

        $config->encrypt($this->createDummyCypher());

        $this->assertSame($expectedDecryptedConfig, $config->getConfig());
    }

    /**
     * @return MockObject|CypherInterface
     */
    private function createDummyCypher()
    {
        $mock = $this->createMock(CypherInterface::class);

        $mock
            ->method('encrypt')
            ->with($this->anything())
            ->willReturnCallback(fn ($value) => 'encrypted-' . $value)
        ;

        $mock
            ->method('decrypt')
            ->with($this->anything())
            ->willReturnCallback(fn ($value) => 'decrypted-' . $value)
        ;

        return $mock;
    }
}
