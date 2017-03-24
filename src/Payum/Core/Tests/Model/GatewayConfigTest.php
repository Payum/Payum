<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\GatewayConfig;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;

class GatewayConfigTest extends \PHPUnit_Framework_TestCase
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
    public function couldBeConstructedWithoutAnyArguments()
    {
        new GatewayConfig();
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetFactoryName()
    {
        $config = new GatewayConfig();

        $config->setFactoryName('theName');

        $this->assertEquals('theName', $config->getFactoryName());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetGatewayName()
    {
        $config = new GatewayConfig();

        $config->setGatewayName('theName');

        $this->assertEquals('theName', $config->getGatewayName());
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

        $this->assertAttributeSame($encryptedConfig, 'config', $config);
        $this->assertAttributeSame($encryptedConfig, 'decryptedConfig', $config);

        $config->decrypt($this->createDummyCypher());

        $this->assertAttributeSame($encryptedConfig, 'config', $config);
        $this->assertAttributeSame($expectedDecryptedConfig, 'decryptedConfig', $config);

        $this->assertSame($expectedDecryptedConfig, $config->getConfig());
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

        $this->assertAttributeSame($plainConfig, 'config', $config);
        $this->assertAttributeSame($plainConfig, 'decryptedConfig', $config);

        $config->decrypt($this->createDummyCypher());

        $this->assertAttributeSame($plainConfig, 'config', $config);
        $this->assertAttributeSame($plainConfig, 'decryptedConfig', $config);

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

        $expectedEncryptedConfig = [
            'foo' => 'encrypted-plainFooVal',
            'bar' => 'encrypted-plainBarVal',
            'encrypted' => true,
        ];


        $config = new GatewayConfig();
        $config->setConfig($plainConfig);

        $this->assertAttributeSame($plainConfig, 'config', $config);
        $this->assertAttributeSame($plainConfig, 'decryptedConfig', $config);

        $config->encrypt($this->createDummyCypher());

        $this->assertAttributeSame($expectedEncryptedConfig, 'config', $config);
        $this->assertAttributeSame($expectedDecryptedConfig, 'decryptedConfig', $config);

        $this->assertSame($expectedDecryptedConfig, $config->getConfig());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CypherInterface
     */
    private function createDummyCypher()
    {
        $mock = $this->getMock(CypherInterface::class);

        $mock
            ->expects($this->any())
            ->method('encrypt')
            ->with($this->anything())
            ->willReturnCallback(function($value) {
                return 'encrypted-'.$value;
            })
        ;

        $mock
            ->expects($this->any())
            ->method('decrypt')
            ->with($this->anything())
            ->willReturnCallback(function($value) {
                return 'decrypted-'.$value;
            })
        ;

        return $mock;
    }
}
