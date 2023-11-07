<?php

namespace Payum\Core\Tests\Bridge\Symfony\Security;

use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenFactoryTest extends TestCase
{
    public function testShouldImplementsTokenFactoryInterface()
    {
        $rc = new ReflectionClass(TokenFactory::class);

        $this->assertTrue($rc->implementsInterface(TokenFactoryInterface::class));
    }

    public function testShouldBeSubClassOfAbtractTokenFactory()
    {
        $rc = new ReflectionClass(TokenFactory::class);

        $this->assertTrue($rc->isSubclassOf(AbstractTokenFactory::class));
    }

    public function testShouldCreateTokenWithoutAfterPath()
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

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $this->createUrlGeneratorStub());

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
            'theTargetPath?payum_token=' . $token->getHash() . '&target=val',
            $token->getTargetUrl()
        );
        $this->assertNull($token->getAfterUrl());
    }

    public function testShouldCreateTokenWithAfterUrl()
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

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $this->createUrlGeneratorStub());

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
            'theTargetPath?payum_token=' . $token->getHash() . '&target=val',
            $token->getTargetUrl()
        );
        $this->assertSame('theAfterPath?after=val', $token->getAfterUrl());
    }

    public function testShouldCreateTokenWithIdentityAsModel()
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

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $this->createUrlGeneratorStub());

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

    public function testShouldCreateTokenWithoutModel()
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

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $this->createUrlGeneratorStub());

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

    public function testShouldCreateTokenWithTargetPathAlreadyUrl()
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

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $this->createUrlGeneratorStub());

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
        $this->assertSame('theAfterPath?after=val', $token->getAfterUrl());
    }

    public function testShouldNotOverwritePayumTokenHashInAfterUrl()
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

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $this->createUrlGeneratorStub());

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

    public function testShouldAllowCreateAfterUrlWithoutPayumToken()
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

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $this->createUrlGeneratorStub());

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

    public function testShouldAllowCreateAfterUrlWithFragment()
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

        $factory = new TokenFactory($tokenStorageMock, $storageRegistryMock, $this->createUrlGeneratorStub());

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
     * @return MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->createMock(StorageInterface::class);
    }

    /**
     * @return MockObject|StorageRegistryInterface
     */
    protected function createStorageRegistryMock()
    {
        return $this->createMock(StorageRegistryInterface::class);
    }

    /**
     * @return MockObject|UrlGeneratorInterface
     */
    protected function createUrlGeneratorStub()
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $urlGenerator
            ->method('generate')
            ->willReturnCallback(fn ($route, $parameters) => $route . '?' . http_build_query($parameters))
        ;

        return $urlGenerator;
    }
}
