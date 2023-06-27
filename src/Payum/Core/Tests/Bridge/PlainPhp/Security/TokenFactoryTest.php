<?php

namespace Payum\Core\Tests\Bridge\PlainPhp\Security;

use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class TokenFactoryTest extends TestCase
{
    public function testShouldImplementsTokenFactoryInterface(): void
    {
        $rc = new ReflectionClass(TokenFactory::class);

        $this->assertTrue($rc->implementsInterface(TokenFactoryInterface::class));
    }

    public function testShouldBeSubClassOfAbtractTokenFactory(): void
    {
        $rc = new ReflectionClass(TokenFactory::class);

        $this->assertTrue($rc->isSubclassOf(AbstractTokenFactory::class));
    }

    public function testShouldCreateTokenWithoutAfterPath(): void
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($token)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $model = new stdClass();
        $identity = new Identity('anId', stdClass::class);
        $gatewayName = 'theGatewayName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->willReturn($identity)
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->willReturn($modelStorage)
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com');

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'theTargetPath',
            [
                'target' => 'val',
            ]
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertSame(
            'http://example.com/theTargetPath?payum_token=' . $token->getHash() . '&target=val',
            $token->getTargetUrl()
        );
        $this->assertNull($token->getAfterUrl());
    }

    public function testShouldCreateTokenWithAfterUrl(): void
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($token)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $model = new stdClass();
        $identity = new Identity('anId', stdClass::class);
        $gatewayName = 'theGatewayName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->willReturn($identity)
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->willReturn($modelStorage)
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com');

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'theTargetPath',
            [
                'target' => 'val',
            ],
            'theAfterPath',
            [
                'after' => 'val',
            ]
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertSame(
            'http://example.com/theTargetPath?payum_token=' . $token->getHash() . '&target=val',
            $token->getTargetUrl()
        );
        $this->assertSame('http://example.com/theAfterPath?after=val', $token->getAfterUrl());
    }

    public function testShouldCreateTokenForSecuredBaseUrl(): void
    {
        $token = new Token();
        $token->setHash('aHash');

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($token)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $gatewayName = 'theGatewayName';
        $identity = new Identity('anId', stdClass::class);

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://aUser@example.com:8080');

        $actualToken = $factory->createToken(
            $gatewayName,
            $identity,
            'theTargetPath',
            [
                'target' => 'val',
            ]
        );

        $this->assertSame(
            'http://aUser@example.com:8080/theTargetPath?payum_token=aHash&target=val',
            $actualToken->getTargetUrl()
        );
    }

    public function testShouldCreateTokenForBaseUrlWithPath(): void
    {
        $token = new Token();
        $token->setHash('aHash');

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($token)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $gatewayName = 'theGatewayName';
        $identity = new Identity('anId', stdClass::class);

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com/aBase/path');

        $actualToken = $factory->createToken(
            $gatewayName,
            $identity,
            'theTargetPath',
            [
                'target' => 'val',
            ]
        );

        $this->assertSame(
            'http://example.com/aBase/path/theTargetPath?payum_token=aHash&target=val',
            $actualToken->getTargetUrl()
        );
    }

    /**
     * @dataProvider pathDataProvider
     */
    public function testShouldCreateTokenForBaseUrlWithPathAndScriptFile(string $hostname, string $target, string $result): void
    {
        $token = new Token();
        $token->setHash('aHash');

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($token)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $gatewayName = 'theGatewayName';
        $identity = new Identity('anId', stdClass::class);

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $hostname);

        $actualToken = $factory->createToken(
            $gatewayName,
            $identity,
            $target,
            [
                'target' => 'val',
            ]
        );

        $this->assertSame(
            $result . '?payum_token=aHash&target=val',
            $actualToken->getTargetUrl()
        );
    }

    public function testShouldCreateTokenWithIdentityAsModel(): void
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($token)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $gatewayName = 'theGatewayName';
        $identity = new Identity('anId', stdClass::class);

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com');

        $actualToken = $factory->createToken(
            $gatewayName,
            $identity,
            'theTargetPath',
            [
                'target' => 'val',
            ],
            'theAfterPath',
            [
                'after' => 'val',
            ]
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($identity, $token->getDetails());
    }

    public function testShouldCreateTokenWithoutModel(): void
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($token)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $gatewayName = 'theGatewayName';

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->never())
            ->method('getStorage')
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com');

        $actualToken = $factory->createToken(
            $gatewayName,
            null,
            'theTargetPath',
            [
                'target' => 'val',
            ],
            'theAfterPath',
            [
                'after' => 'val',
            ]
        );

        $this->assertSame($token, $actualToken);
        $this->assertNull($token->getDetails());
    }

    public function testShouldCreateTokenWithTargetPathAlreadyUrl(): void
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($token)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($token))
        ;

        $model = new stdClass();
        $identity = new Identity('anId', stdClass::class);
        $gatewayName = 'theGatewayName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->willReturn($identity)
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->willReturn($modelStorage)
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com');

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'http://google.com/?foo=fooVal',
            [
                'target' => 'val',
            ],
            'theAfterPath',
            [
                'after' => 'val',
            ]
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertSame(
            'http://google.com/?foo=fooVal&payum_token=' . $token->getHash() . '&target=val',
            $token->getTargetUrl()
        );
        $this->assertSame('http://example.com/theAfterPath?after=val', $token->getAfterUrl());
    }

    public function testShouldNotOverwritePayumTokenHashInAfterUrl(): void
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($authorizeToken)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new stdClass();
        $identity = new Identity('anId', stdClass::class);
        $gatewayName = 'theGatewayName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->willReturn($identity)
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->willReturn($modelStorage)
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com');

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'http://example.com/authorize.php',
            [],
            'http://google.com/?payum_token=foo',
            [
                'afterKey' => 'afterVal',
            ]
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertSame($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertSame(
            'http://example.com/authorize.php?payum_token=' . $authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertSame(
            'http://google.com/?payum_token=foo&afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    public function testShouldAllowCreateAfterUrlWithoutPayumToken(): void
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($authorizeToken)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new stdClass();
        $identity = new Identity('anId', stdClass::class);
        $gatewayName = 'theGatewayName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->willReturn($identity)
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->willReturn($modelStorage)
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com');

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'http://example.com/authorize.php',
            [],
            'http://google.com/?payum_token=foo',
            [
                'payum_token' => null,
                'afterKey' => 'afterVal',
            ]
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertSame($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertSame(
            'http://example.com/authorize.php?payum_token=' . $authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertSame(
            'http://google.com/?afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    public function testShouldAllowCreateAfterUrlWithFragment(): void
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($authorizeToken)
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new stdClass();
        $identity = new Identity('anId', stdClass::class);
        $gatewayName = 'theGatewayName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('identify')
            ->with($this->identicalTo($model))
            ->willReturn($identity)
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->willReturn($modelStorage)
        ;

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, 'http://example.com');

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'http://example.com/authorize.php',
            [],
            'http://google.com/foo/bar?foo=fooVal#fragment',
            [
                'payum_token' => null,
            ]
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertSame($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertSame(
            'http://example.com/authorize.php?payum_token=' . $authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertSame(
            'http://google.com/foo/bar?foo=fooVal#fragment',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @return array<int, array<string>>
     */
    public function pathDataProvider(): array
    {
        return [
            ['http://example.com', 'capture.php', 'http://example.com/capture.php'],
            ['http://example.com/path', 'capture.php', 'http://example.com/path/capture.php'],
            ['http://example.com/path/anotherPath', 'capture.php', 'http://example.com/path/anotherPath/capture.php'],

            ['http://example.com', 'capture', 'http://example.com/capture'],
            ['http://example.com/path', 'capture', 'http://example.com/path/capture'],
            ['http://example.com/path/anotherPath', 'capture', 'http://example.com/path/anotherPath/capture'],

            ['http://example.com/index.php', 'capture.php', 'http://example.com/capture.php'],
            ['http://example.com/path/index.php', 'capture.php', 'http://example.com/path/capture.php'],
            ['http://example.com/path/anotherPath/index.php', 'capture.php', 'http://example.com/path/anotherPath/capture.php'],

            ['http://example.com/index.php', 'capture', 'http://example.com/capture'],
            ['http://example.com/path/index.php', 'capture', 'http://example.com/path/capture'],
            ['http://example.com/path/anotherPath/index.php', 'capture', 'http://example.com/path/anotherPath/capture'],
        ];
    }

    /**
     * @return MockObject | StorageInterface<TokenInterface>
     */
    protected function createStorageMock()
    {
        return $this->createMock(StorageInterface::class);
    }

    /**
     * @return MockObject | StorageRegistryInterface<TokenInterface>
     */
    protected function createStorageRegistryMock()
    {
        return $this->createMock(StorageRegistryInterface::class);
    }
}
