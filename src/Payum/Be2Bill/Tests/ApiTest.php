<?php

namespace Payum\Be2Bill\Tests;

use Http\Discovery\Psr17FactoryDiscovery;
use Payum\Be2Bill\Api;
use Payum\Core\Exception\LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ApiTest extends TestCase
{
    public function testThrowIfRequiredOptionsNotSetInConstructor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The identifier, password fields are required.');
        new Api([], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
    }

    public function testThrowIfSandboxOptionsNotBooleanInConstructor(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The boolean sandbox option must be set.');
        new Api([
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => 'notABool',
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());
    }

    public function testShouldReturnPostArrayWithOperationTypeAddedOnPrepareOffsitePayment(): void
    {
        $api = new Api([
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $post = $api->prepareOffsitePayment([
            'AMOUNT' => 100,
        ]);

        $this->assertIsArray($post);
        $this->assertArrayHasKey('OPERATIONTYPE', $post);
        $this->assertSame(Api::OPERATION_PAYMENT, $post['OPERATIONTYPE']);
    }

    public function testShouldReturnPostArrayWithGlobalsAddedOnPrepareOffsitePayment(): void
    {
        $api = new Api([
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $post = $api->prepareOffsitePayment([
            'AMOUNT' => 100,
        ]);

        $this->assertIsArray($post);
        $this->assertArrayHasKey('VERSION', $post);
        $this->assertArrayHasKey('IDENTIFIER', $post);
        $this->assertArrayHasKey('HASH', $post);
    }

    public function testShouldFilterNotSupportedOnPrepareOffsitePayment(): void
    {
        $api = new Api([
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $post = $api->prepareOffsitePayment([
            'AMOUNT' => 100,
            'FOO' => 'fooVal',
            'BAR' => 'barVal',
        ]);

        $this->assertIsArray($post);
        $this->assertArrayNotHasKey('FOO', $post);
        $this->assertArrayNotHasKey('BAR', $post);
    }

    public function testShouldKeepSupportedOnPrepareOffsitePayment(): void
    {
        $api = new Api([
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $post = $api->prepareOffsitePayment([
            'AMOUNT' => 100,
            'DESCRIPTION' => 'a desc',
        ]);

        $this->assertIsArray($post);

        $this->assertArrayHasKey('AMOUNT', $post);
        $this->assertSame(100, $post['AMOUNT']);

        $this->assertArrayHasKey('DESCRIPTION', $post);
        $this->assertSame('a desc', $post['DESCRIPTION']);
    }

    public function testShouldReturnFalseIfHashNotSetToParams(): void
    {
        $api = new Api([
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $this->assertFalse($api->verifyHash([]));
    }

    public function testShouldReturnFalseIfHashesMisMatched(): void
    {
        $params = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];
        $invalidHash = 'invalidHash';

        $api = new Api([
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        //guard
        $this->assertNotSame($invalidHash, $api->calculateHash($params));

        $params['HASH'] = $invalidHash;

        $this->assertFalse($api->verifyHash($params));
    }

    public function testShouldReturnTrueIfHashesMatched(): void
    {
        $params = [
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ];

        $api = new Api([
            'identifier' => 'anId',
            'password' => 'aPass',
            'sandbox' => true,
        ], $this->createHttpClientMock(), $this->createHttpMessageFactory(), $this->createHttpStreamFactory());

        $params['HASH'] = $api->calculateHash($params);

        $this->assertTrue($api->verifyHash($params));
    }

    protected function createHttpClientMock(): MockObject | ClientInterface
    {
        return $this->createMock(ClientInterface::class);
    }

    protected function createHttpMessageFactory(): RequestFactoryInterface
    {
        return Psr17FactoryDiscovery::findRequestFactory();
    }

    protected function createHttpStreamFactory(): StreamFactoryInterface
    {
        return Psr17FactoryDiscovery::findStreamFactory();
    }
}
