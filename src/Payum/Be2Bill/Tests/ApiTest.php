<?php
namespace Payum\Be2Bill\Tests;

use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Be2Bill\Api;
use Payum\Core\HttpClientInterface;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    /**
     * @test
     */
    public function throwIfRequiredOptionsNotSetInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The identifier, password fields are required.');
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    /**
     * @test
     */
    public function throwIfSandboxOptionsNotBooleanInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => 'notABool'
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
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
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $post = $api->prepareOffsitePayment(array(
            'AMOUNT' => 100,
        ));

        $this->assertIsArray($post);
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
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $post = $api->prepareOffsitePayment(array(
            'AMOUNT' => 100,
        ));

        $this->assertIsArray($post);
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
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $post = $api->prepareOffsitePayment(array(
            'AMOUNT' => 100,
            'FOO' => 'fooVal',
            'BAR' => 'barVal',
        ));

        $this->assertIsArray($post);
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
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $post = $api->prepareOffsitePayment(array(
            'AMOUNT' => 100,
            'DESCRIPTION' => 'a desc',
        ));

        $this->assertIsArray($post);

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
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

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
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

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
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $params['HASH'] = $api->calculateHash($params);

        $this->assertTrue($api->verifyHash($params));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|HttpClientInterface
     */
    protected function createHttpClientMock()
    {
        return $this->createMock('Payum\Core\HttpClientInterface');
    }

    /**
     * @return \Http\Message\MessageFactory
     */
    protected function createHttpMessageFactory()
    {
        return new GuzzleMessageFactory();
    }
}
