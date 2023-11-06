<?php
namespace Payum\Be2Bill\Tests;

use Http\Message\MessageFactory\GuzzleMessageFactory;
use Payum\Be2Bill\Api;
use Payum\Core\HttpClientInterface;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    public function testThrowIfRequiredOptionsNotSetInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The identifier, password fields are required.');
        new Api(array(), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testThrowIfSandboxOptionsNotBooleanInConstructor()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => 'notABool'
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());
    }

    public function testShouldReturnPostArrayWithOperationTypeAddedOnPrepareOffsitePayment()
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
        $this->assertSame(Api::OPERATION_PAYMENT, $post['OPERATIONTYPE']);
    }

    public function testShouldReturnPostArrayWithGlobalsAddedOnPrepareOffsitePayment()
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

    public function testShouldFilterNotSupportedOnPrepareOffsitePayment()
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

    public function testShouldKeepSupportedOnPrepareOffsitePayment()
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
        $this->assertSame(100, $post['AMOUNT']);

        $this->assertArrayHasKey('DESCRIPTION', $post);
        $this->assertSame('a desc', $post['DESCRIPTION']);
    }

    public function testShouldReturnFalseIfHashNotSetToParams()
    {
        $api = new Api(array(
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ), $this->createHttpClientMock(), $this->createHttpMessageFactory());

        $this->assertFalse($api->verifyHash(array()));
    }

    public function testShouldReturnFalseIfHashesMisMatched()
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
        $this->assertNotSame($invalidHash, $api->calculateHash($params));

        $params['HASH'] = $invalidHash;

        $this->assertFalse($api->verifyHash($params));
    }

    public function testShouldReturnTrueIfHashesMatched()
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
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpClientInterface
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
