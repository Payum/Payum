<?php
namespace Payum\Payex\Tests\Api;

use Payum\Payex\Api\SoapClientFactory;

class SoapClientFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new SoapClientFactory();
    }

    /**
     * @test
     */
    public function shouldAllowCreateSoapClientWithDefaultClassAndOptions()
    {
        $factory = new SoapClientFactory();

        $client = $factory->createWsdlClient('https://test-external.payex.com/pxorder/pxorder.asmx?wsdl');

        $this->assertInstanceOf('SoapClient', $client);
    }

    /**
     * @test
     */
    public function shouldAllowCreateSoapClientWithCustomClassAndOptions()
    {
        $options = array(
            'trace' => true,
            'exceptions' => true,
        );

        $factory = new SoapClientFactory($options, 'Payum\Payex\Tests\Api\CustomSoapClient');

        $client = $factory->createWsdlClient('https://test-external.payex.com/pxorder/pxorder.asmx?wsdl');

        $this->assertInstanceOf('Payum\Payex\Tests\Api\CustomSoapClient', $client);
    }
}

class CustomSoapClient extends \SoapClient
{
}
