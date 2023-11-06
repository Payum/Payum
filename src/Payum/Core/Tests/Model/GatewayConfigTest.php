<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\GatewayConfig;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use PHPUnit\Framework\TestCase;

class GatewayConfigTest extends TestCase
{
    public function testShouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass(GatewayConfig::class);

        $this->assertTrue($rc->implementsInterface(GatewayConfigInterface::class));
    }

    public function testShouldImplementCryptedInterface()
    {
        $rc = new \ReflectionClass(GatewayConfig::class);

        $this->assertTrue($rc->implementsInterface(CryptedInterface::class));
    }

    public function testShouldAllowGetPreviouslySetFactoryName()
    {
        $config = new GatewayConfig();

        $config->setFactoryName('theName');

        $this->assertSame('theName', $config->getFactoryName());
    }

    public function testShouldAllowGetPreviouslySetGatewayName()
    {
        $config = new GatewayConfig();

        $config->setGatewayName('theName');

        $this->assertSame('theName', $config->getGatewayName());
    }

    public function testShouldAllowGetDefaultConfigSetInConstructor()
    {
        $config = new GatewayConfig();

        $this->assertSame([], $config->getConfig());
    }

    public function testShouldAllowGetPreviouslySetConfig()
    {
        $config = new GatewayConfig();

        $config->setConfig(array('foo' => 'fooVal'));

        $this->assertSame(array('foo' => 'fooVal'), $config->getConfig());
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
     * @return \PHPUnit_Framework_MockObject_MockObject|CypherInterface
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
