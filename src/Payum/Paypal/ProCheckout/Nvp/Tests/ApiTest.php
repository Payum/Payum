<?php
namespace Payum\Paypal\ProCheckout\Nvp\Tests;

use GuzzleHttp\Psr7\Response;
use Payum\Core\HttpClientInterface;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Psr\Http\Message\RequestInterface;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOptionsOnly()
    {
        $api = new Api(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender'
        ));

        $this->assertAttributeInstanceOf('Payum\Core\HttpClientInterface', 'client', $api);
    }

    /**
     * @test
     */
    public function couldBeConstructedWithOptionsAndHttpClient()
    {
        $client = $this->createHttpClientMock();

        $api = new Api(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender'
        ), $client);

        $this->assertAttributeSame($client, 'client', $api);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The username, password, partner, vendor fields are required.
     */
    public function throwIfRequiredOptionsNotSetInConstructor()
    {
        new Api(array());
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The boolean sandbox option must be set.
     */
    public function throwIfSandboxOptionsNotBooleanInConstructor()
    {
        new Api(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender',
            'sandbox' => 'notABool'
        ));
    }

    /**
     * @test
     */
    public function shouldAddTRXTYPEOnDoSaleCall()
    {
        $api = new Api(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender'
        ), $this->createSuccessHttpClientStub());

        $result = $api->doSale(array());

        $this->assertArrayHasKey('TRXTYPE', $result);
        $this->assertEquals(Api::TRXTYPE_SALE, $result['TRXTYPE']);
    }

    /**
     * @test
     */
    public function shouldAddTRXTYPEOnDoCreditCall()
    {
        $api = new Api(array(
            'username' => 'aUsername',
            'password' => 'aPassword',
            'partner' => 'aPartner',
            'vendor' => 'aVendor',
            'tender' => 'aTender'
        ), $this->createSuccessHttpClientStub());

        $result = $api->doCredit(array());

        $this->assertArrayHasKey('TRXTYPE', $result);
        $this->assertEquals(Api::TRXTYPE_CREDIT, $result['TRXTYPE']);
    }

    /**
     * @test
     */
    public function shouldAddAuthorizeFieldsOnDoSaleCall()
    {
        $api = new Api(array(
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender'
        ), $this->createSuccessHttpClientStub());

        $result = $api->doSale(array());

        $this->assertArrayHasKey('USER', $result);
        $this->assertEquals('theUsername', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertEquals('thePassword', $result['PWD']);

        $this->assertArrayHasKey('PARTNER', $result);
        $this->assertEquals('thePartner', $result['PARTNER']);

        $this->assertArrayHasKey('VENDOR', $result);
        $this->assertEquals('theVendor', $result['VENDOR']);

        $this->assertArrayHasKey('TENDER', $result);
        $this->assertEquals('theTender', $result['TENDER']);
    }

    /**
     * @test
     */
    public function shouldAddAuthorizeFieldsOnDoCreditCall()
    {
        $api = new Api(array(
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender'
        ), $this->createSuccessHttpClientStub());

        $result = $api->doCredit(array());

        $this->assertArrayHasKey('USER', $result);
        $this->assertEquals('theUsername', $result['USER']);

        $this->assertArrayHasKey('PWD', $result);
        $this->assertEquals('thePassword', $result['PWD']);

        $this->assertArrayHasKey('PARTNER', $result);
        $this->assertEquals('thePartner', $result['PARTNER']);

        $this->assertArrayHasKey('VENDOR', $result);
        $this->assertEquals('theVendor', $result['VENDOR']);

        $this->assertArrayHasKey('TENDER', $result);
        $this->assertEquals('theTender', $result['TENDER']);
    }

    /**
     * @test
     */
    public function shouldUseRealApiEndpointIfSandboxFalse()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertEquals('https://payflowpro.paypal.com/', $request->getUri());

                return new Response(200, [], $request->getBody());
            }))
        ;

        $api = new Api(array(
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
            'sandbox' => false,
        ), $clientMock);

        $api->doCredit(array());
    }

    /**
     * @test
     */
    public function shouldUseSandboxApiEndpointIfSandboxTrue()
    {
        $testCase = $this;

        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) use ($testCase) {
                $testCase->assertEquals('https://pilot-payflowpro.paypal.com/', $request->getUri());

                return new Response(200, [], $request->getBody());
            }))
        ;

        $api = new Api(array(
            'username' => 'theUsername',
            'password' => 'thePassword',
            'partner' => 'thePartner',
            'vendor' => 'theVendor',
            'tender' => 'theTender',
            'sandbox' => true,
        ), $clientMock);

        $api->doCredit(array());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->getMock('Payum\Core\HttpClientInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createSuccessHttpClientStub()
    {
        $clientMock = $this->createHttpClientMock();
        $clientMock
            ->expects($this->any())
            ->method('send')
            ->will($this->returnCallback(function (RequestInterface $request) {
                return new Response(200, [], $request->getBody());
            }))
        ;

        return $clientMock;
    }
}
