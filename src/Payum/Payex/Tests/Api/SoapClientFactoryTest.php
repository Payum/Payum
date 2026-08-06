<?php

declare(strict_types=1);

namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\SoapClientFactory;
use PHPUnit\Framework\TestCase;
use SoapClient;

final class SoapClientFactoryTest extends TestCase
{
    public function testShouldAllowCreateSoapClientWithDefaultClassAndOptions(): void
    {
        if (! getenv('PAYUM_TEST_EXTERNAL_SERVICES')) {
            $this->markTestSkipped('Set PAYUM_TEST_EXTERNAL_SERVICES to run tests that call an external service.');
        }

        $factory = new SoapClientFactory();

        $client = $factory->createWsdlClient('https://external.externaltest.payex.com/pxorder/pxorder.asmx?WSDL');

        $this->assertInstanceOf(SoapClient::class, $client);
    }

    public function testShouldAllowCreateSoapClientWithCustomClassAndOptions(): void
    {
        if (! getenv('PAYUM_TEST_EXTERNAL_SERVICES')) {
            $this->markTestSkipped('Set PAYUM_TEST_EXTERNAL_SERVICES to run tests that call an external service.');
        }

        $options = [
            'trace' => true,
            'exceptions' => true,
        ];

        $factory = new SoapClientFactory($options, CustomSoapClient::class);

        $client = $factory->createWsdlClient('https://external.externaltest.payex.com/pxorder/pxorder.asmx?WSDL');

        $this->assertInstanceOf(CustomSoapClient::class, $client);
    }
}

class CustomSoapClient extends SoapClient
{
}
