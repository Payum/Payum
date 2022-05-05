<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\GatewayConfig;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class GatewayConfigTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass(GatewayConfig::class);

        $this->assertTrue($rc->implementsInterface(GatewayConfigInterface::class));
    }

    /**
     * @test
     */
    public function shouldImplementCryptedInterface()
    {
        $rc = new \ReflectionClass(GatewayConfig::class);

        $this->assertTrue($rc->implementsInterface(CryptedInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetFactoryName()
    {
        $config = new GatewayConfig();

        $config->setFactoryName('theName');

        $this->assertSame('theName', $config->getFactoryName());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetGatewayName()
    {
        $config = new GatewayConfig();

        $config->setGatewayName('theName');

        $this->assertSame('theName', $config->getGatewayName());
    }

    /**
     * @test
     */
    public function shouldAllowGetDefaultConfigSetInConstructor()
    {
        $config = new GatewayConfig();

        $this->assertEquals([], $config->getConfig());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetConfig()
    {
        $config = new GatewayConfig();

        $config->setConfig(array('foo' => 'fooVal'));

        $this->assertEquals(array('foo' => 'fooVal'), $config->getConfig());
    }

    public function testShouldDecryptConfigValuesOnDecrypt()
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

        $this->assertSame($encryptedConfig,  $config->getConfig());

        $config->decrypt($this->createDummyCypher());

        $this->assertSame($expectedDecryptedConfig,  $config->getConfig());
    }

    public function testShouldDoNothingOnDecryptIfConfigIsNotEncrypted()
    {
        $plainConfig = [
            'encrypted' => false,
            'foo' => 'encryptedFooVal',
            'bar' => 'encryptedBarVal',
        ];

        $config = new GatewayConfig();
        $config->setConfig($plainConfig);

        $this->assertSame($plainConfig,  $config->getConfig());

        $config->decrypt($this->createDummyCypher());

        $this->assertSame($plainConfig, $config->getConfig());
    }

    public function testShouldEncryptConfigValuesOnEncrypt()
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
            ->willReturnCallback(function($value) {
                return 'encrypted-'.$value;
            })
        ;

        $mock
            ->method('decrypt')
            ->with($this->anything())
            ->willReturnCallback(function($value) {
                return 'decrypted-'.$value;
            })
        ;

        return $mock;
    }
}
