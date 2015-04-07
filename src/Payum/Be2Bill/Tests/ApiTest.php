<?php
namespace Payum\Be2Bill\Tests;

use Buzz\Client\ClientInterface;
use Payum\Be2Bill\Api;

class ApiTest extends \Phpunit_Framework_TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithClientAndOptions()
    {
        new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createClientMock());
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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

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
        ), $this->createClientMock());

        $params['HASH'] = $api->calculateHash($params);

        $this->assertTrue($api->verifyHash($params));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    protected function createClientMock()
    {
        return $this->getMock('Buzz\Client\ClientInterface');
    }
}
