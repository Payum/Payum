<?php
namespace Payum\Core\Tests\Bridge\PlainPhp\Security;

use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class TokenFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementsTokenFactoryInterface(): void
    {
        $rc = new \ReflectionClass(TokenFactory::class);

        $this->assertTrue($rc->implementsInterface(TokenFactoryInterface::class));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfAbtractTokenFactory(): void
    {
        $rc = new \ReflectionClass(TokenFactory::class);

        $this->assertTrue($rc->isSubclassOf(AbstractTokenFactory::class));
    }

    /**
     * @test
     */
    public function shouldCreateTokenWithoutAfterPath()
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

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
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
            array('target' => 'val')
        );

        $this->assertSame($token, $actualToken);
        $this->assertEquals($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals(
            'http://example.com/theTargetPath?payum_token='.$token->getHash().'&target=val',
            $token->getTargetUrl()
        );
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateTokenWithAfterUrl(): void
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

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
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
            array('target' => 'val'),
            'theAfterPath',
            array('after' => 'val')
        );

        $this->assertSame($token, $actualToken);
        $this->assertEquals($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals(
            'http://example.com/theTargetPath?payum_token='.$token->getHash().'&target=val',
            $token->getTargetUrl()
        );
        $this->assertEquals('http://example.com/theAfterPath?after=val', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateTokenForSecuredBaseUrl(): void
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
        $identity = new Identity('anId', 'stdClass');

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
            ['target' => 'val']
        );

        $this->assertEquals(
            'http://aUser@example.com:8080/theTargetPath?payum_token=aHash&target=val',
            $actualToken->getTargetUrl()
        );
    }

    /**
     * @test
     */
    public function shouldCreateTokenForBaseUrlWithPath(): void
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
        $identity = new Identity('anId', 'stdClass');

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
            ['target' => 'val']
        );

        $this->assertEquals(
            'http://example.com/aBase/path/theTargetPath?payum_token=aHash&target=val',
            $actualToken->getTargetUrl()
        );
    }

    /**
     * @test
     * @dataProvider pathDataProvider
     */
    public function shouldCreateTokenForBaseUrlWithPathAndScriptFile($hostname, $target, $result): void
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
        $identity = new Identity('anId', 'stdClass');

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
            ['target' => 'val']
        );

        $this->assertEquals(
            $result.'?payum_token=aHash&target=val',
            $actualToken->getTargetUrl()
        );
    }

    /**
     * @test
     */
    public function shouldCreateTokenWithIdentityAsModel(): void
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
        $identity = new Identity('anId', 'stdClass');

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
            array('target' => 'val'),
            'theAfterPath',
            array('after' => 'val')
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($identity, $token->getDetails());
    }

    /**
     * @test
     */
    public function shouldCreateTokenWithoutModel(): void
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
            array('target' => 'val'),
            'theAfterPath',
            array('after' => 'val')
        );

        $this->assertSame($token, $actualToken);
        $this->assertNull($token->getDetails());
    }

    /**
     * @test
     */
    public function shouldCreateTokenWithTargetPathAlreadyUrl(): void
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

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
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
            array('target' => 'val'),
            'theAfterPath',
            array('after' => 'val')
        );

        $this->assertSame($token, $actualToken);
        $this->assertEquals($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals(
            'http://google.com/?foo=fooVal&payum_token='.$token->getHash().'&target=val',
            $token->getTargetUrl()
        );
        $this->assertEquals('http://example.com/theAfterPath?after=val', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldNotOverwritePayumTokenHashInAfterUrl(): void
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->willReturn($authorizeToken)
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
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
            array(),
            'http://google.com/?payum_token=foo',
            array('afterKey' => 'afterVal')
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://google.com/?payum_token=foo&afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateAfterUrlWithoutPayumToken(): void
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->willReturn($authorizeToken)
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
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
            ['payum_token' => null, 'afterKey' => 'afterVal']
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://google.com/?afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateAfterUrlWithFragment(): void
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->willReturn($authorizeToken)
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
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
            array(),
            'http://google.com/foo/bar?foo=fooVal#fragment',
            array('payum_token' => null)
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertEquals(
            'http://google.com/foo/bar?foo=fooVal#fragment',
            $authorizeToken->getAfterUrl()
        );
    }

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

    protected function createStorageMock(): StorageInterface|MockObject
    {
        return $this->createMock('Payum\Core\Storage\StorageInterface');
    }

    protected function createStorageRegistryMock(): MockObject|StorageRegistryInterface
    {
        return $this->createMock('Payum\Core\Registry\StorageRegistryInterface');
    }
}
