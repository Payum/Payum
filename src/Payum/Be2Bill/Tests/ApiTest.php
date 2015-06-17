<?php
namespace Payum\Be2Bill\Tests;

use Payum\Be2Bill\Api;
use Payum\Core\HttpClientInterface;

class ApiTest extends \Phpunit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithOptionsOnly()
    {
        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
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
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $client);

        $this->assertAttributeSame($client, 'client', $api);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The identifier, password fields are required.
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
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => 'notABool'
        ));
    }

    /**
     * @test
     */
    public function shouldReturnPostArrayWithOperationTypeAddedOnPrepareOffsitePayment()
    {
        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $post = $api->prepareOffsitePayment(array(
            'AMOUNT' => 100,
        ));

        $this->assertInternalType('array', $post);
        $this->assertArrayHasKey('OPERATIONTYPE', $post);
        $this->assertEquals(Api::OPERATION_PAYMENT, $post['OPERATIONTYPE']);
    }

    /**
     * @test
     */
    public function shouldReturnPostArrayWithGlobalsAddedOnPrepareOffsitePayment()
    {
        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $post = $api->prepareOffsitePayment(array(
            'AMOUNT' => 100,
        ));

        $this->assertInternalType('array', $post);
        $this->assertArrayHasKey('VERSION', $post);
        $this->assertArrayHasKey('IDENTIFIER', $post);
        $this->assertArrayHasKey('HASH', $post);
    }

    /**
     * @test
     */
    public function shouldFilterNotSupportedOnPrepareOffsitePayment()
    {
        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $post = $api->prepareOffsitePayment(array(
            'AMOUNT' => 100,
            'FOO' => 'fooVal',
            'BAR' => 'barVal',
        ));

        $this->assertInternalType('array', $post);
        $this->assertArrayNotHasKey('FOO', $post);
        $this->assertArrayNotHasKey('BAR', $post);
    }

    /**
     * @test
     */
    public function shouldKeepSupportedOnPrepareOffsitePayment()
    {
        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $post = $api->prepareOffsitePayment(array(
            'AMOUNT' => 100,
            'DESCRIPTION' => 'a desc',
        ));

        $this->assertInternalType('array', $post);

        $this->assertArrayHasKey('AMOUNT', $post);
        $this->assertEquals(100, $post['AMOUNT']);

        $this->assertArrayHasKey('DESCRIPTION', $post);
        $this->assertEquals('a desc', $post['DESCRIPTION']);
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfHashNotSetToParams()
    {
        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $this->assertFalse($api->verifyHash(array()));
    }

    /**
     * @test
     */
    public function shouldReturnFalseIfHashesMisMatched()
    {
        $params = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );
        $invalidHash = 'invalidHash';

        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        //guard
        $this->assertNotEquals($invalidHash, $api->calculateHash($params));

        $params['HASH'] = $invalidHash;

        $this->assertFalse($api->verifyHash($params));
    }

    /**
     * @test
     */
    public function shouldReturnTrueIfHashesMatched()
    {
        $params = array(
            'foo' => 'fooVal',
            'bar' => 'barVal',
        );

        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createHttpClientMock());

        $params['HASH'] = $api->calculateHash($params);

        $this->assertTrue($api->verifyHash($params));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->getMock('Payum\Core\HttpClientInterface');
    }
}
