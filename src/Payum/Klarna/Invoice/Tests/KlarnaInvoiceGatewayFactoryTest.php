<?php

namespace Payum\Klarna\Invoice\Tests;

use Payum\Core\Exception\LogicException;
use Payum\Core\Tests\AbstractGatewayFactoryTest;
use Payum\Klarna\Invoice\KlarnaInvoiceGatewayFactory;

class KlarnaInvoiceGatewayFactoryTest extends AbstractGatewayFactoryTest
{
    public function testShouldAddDefaultConfigPassedInConstructorWhileCreatingGatewayConfig(): void
    {
        $factory = new KlarnaInvoiceGatewayFactory([
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

    public function testShouldConfigContainDefaultOptions(): void
    {
        $factory = new KlarnaInvoiceGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.default_options', $config);
        $this->assertEquals(
            [
                'eid' => '',
                'secret' => '',
                'country' => '',
                'language' => '',
                'currency' => '',
                'sandbox' => true,
            ],
            $config['payum.default_options']
        );
    }

    public function testShouldConfigContainFactoryNameAndTitle(): void
    {
        $factory = new KlarnaInvoiceGatewayFactory();

        $config = $factory->createConfig();

        $this->assertIsArray($config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertSame('klarna_invoice', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertSame('Klarna Invoice', $config['payum.factory_title']);
    }

    public function testShouldThrowIfRequiredOptionsNotPassed(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The eid, secret, country, language, currency fields are required.');
        $factory = new KlarnaInvoiceGatewayFactory();

        $factory->create();
    }

    protected function getGatewayFactoryClass(): string
    {
        return KlarnaInvoiceGatewayFactory::class;
    }

    protected function getRequiredOptions(): array
    {
        return [
            'eid' => 'aEID',
            'secret' => 'aSecret',
            'country' => 'SV',
            'language' => 'SE',
            'currency' => 'SEK',
        ];
    }
}
