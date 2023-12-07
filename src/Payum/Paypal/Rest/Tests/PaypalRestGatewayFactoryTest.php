<?php

namespace Payum\Paypal\Rest\Tests;

use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Paypal\Rest\PaypalRestGatewayFactory;

class PaypalRestGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    protected function getGatewayFactoryClass(): string
    {
        return PaypalRestGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'client_id' => 'cId',
            'client_secret' => 'cSecret',
            'config_path' => __DIR__,
        ];
    }

    public function testShouldAllowCreateGatewayWithCustomConfig()
    {
        $factory = new PaypalRestGatewayFactory();

        $givenConfig = [
            'log.LogLevel' => 'DEBUG',
            'mode' => 'live',
            'log.FileName' => '/foo/bar.log',
            'http.ConnectionTimeOut' => '10',
        ];

        $gateway = $factory->create([
            'client_id' => 'cId',
            'client_secret' => 'cSecret',
            'config' => $givenConfig,
        ]);

        $apis = $this->getPropertyValue($gateway, 'apis');
        $apiContext = null;
        foreach ($apis as $api) {
            if ($api instanceof \PayPal\Rest\ApiContext) {
                $apiContext = $api;
                break;
            }
        }

        $this->assertNotNull($apiContext);

        $apiContextConfig = $apiContext->getConfig();
        foreach ($givenConfig as $k => $v) {
            $this->assertArrayHasKey($k, $apiContextConfig);
            $this->assertSame($v, $apiContextConfig[$k]);
        }
    }

    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig()
    {
        $factory = new PaypalRestGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertSame('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertSame('barVal', $config['bar']);
    }

    public function testShouldConfigContainDefaultOptions()
    {
        $factory = new PaypalRestGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertSame(['client_id' => '', 'client_secret' => '', 'config_path' => '', 'config' => []], $config['payum.default_options']);
    }

    public function testShouldConfigContainFactoryNameAndTitle()
    {
        $factory = new PaypalRestGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('paypal_rest', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('PayPal Rest', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The client_id, client_secret fields are required.');
        $factory = new PaypalRestGatewayFactory();

        $factory->create();
    }

    public function testShouldThrowIfConfigPathOptionsNotEqualPaypalPath()
    {
        define('PP_CONFIG_PATH', __DIR__);
        $this->expectException(\Payum\Core\Exception\InvalidArgumentException::class);

        if (method_exists($this, 'expectExceptionMessageRegExp')) {
            $this->expectExceptionMessageMatches('/Given \"config_path\" is invalid. \w+/');
        } else {
            $this->expectExceptionMessageMatches('/Given \"config_path\" is invalid. \w+/');
        }

        $factory = new PaypalRestGatewayFactory();
        $factory->create([
            'client_id' => 'cId',
            'client_secret' => 'cSecret',
            'config_path' => dirname(__DIR__),
        ]);
    }
}
