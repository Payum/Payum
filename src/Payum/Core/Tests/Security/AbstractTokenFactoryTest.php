<?php
namespace Payum\Core\Tests\Security;

use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

class AbstractTokenFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementsGenericTokenFactoryInterface()
    {
        $rc = new \ReflectionClass(AbstractTokenFactory::class);

        $this->assertTrue($rc->implementsInterface(TokenFactoryInterface::class));
    }

    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass(AbstractTokenFactory::class);

        $this->assertFalse($rc->isInstantiable());
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

        $factory = $this->createTokenFactoryMock($tokenStorageMock, $storageRegistryMock);

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'theTargetPath',
            array('target' => 'val')
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertSame(
            'theTargetPath?payum_token='.$token->getHash().'&target=val',
            $token->getTargetUrl()
        );
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateTokenWithAfterUrl()
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

        $factory = $this->createTokenFactoryMock($tokenStorageMock, $storageRegistryMock);

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'theTargetPath',
            array('target' => 'val'),
            'theAfterPath',
            array('after' => 'val')
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertSame(
            'theTargetPath?payum_token='.$token->getHash().'&target=val',
            $token->getTargetUrl()
        );
        $this->assertSame('theAfterPath?after=val', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateTokenWithIdentityAsModel()
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

        $factory = $this->createTokenFactoryMock($tokenStorageMock, $storageRegistryMock);

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
    public function shouldCreateTokenWithoutModel()
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

        $factory = $this->createTokenFactoryMock($tokenStorageMock, $storageRegistryMock);

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
    public function shouldCreateTokenWithTargetPathAlreadyUrl()
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

        $factory = $this->createTokenFactoryMock($tokenStorageMock, $storageRegistryMock);

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'http://google.com/?foo=fooVal',
            array('target' => 'val'),
            'theAfterPath',
            array('after' => 'val')
        );

        $this->assertSame($token, $actualToken);
        $this->assertSame($gatewayName, $token->getGatewayName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertSame(
            'http://google.com/?foo=fooVal&payum_token='.$token->getHash().'&target=val',
            $token->getTargetUrl()
        );
        $this->assertSame('theAfterPath?after=val', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldNotOverwritePayumTokenHashInAfterUrl()
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

        $factory = $this->createTokenFactoryMock($tokenStorageMock, $storageRegistryMock);

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'http://example.com/authorize.php',
            [],
            'http://google.com/?payum_token=foo',
            array('afterKey' => 'afterVal')
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertSame($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertSame(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertSame(
            'http://google.com/?payum_token=foo&afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateAfterUrlWithoutPayumToken()
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

        $factory = $this->createTokenFactoryMock($tokenStorageMock, $storageRegistryMock);

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'http://example.com/authorize.php',
            [],
            'http://google.com/?payum_token=foo',
            array('payum_token' => null, 'afterKey' => 'afterVal')
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertSame($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertSame(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertSame(
            'http://google.com/?afterKey=afterVal',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateAfterUrlWithFragment()
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

        $factory = $this->createTokenFactoryMock($tokenStorageMock, $storageRegistryMock);

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            'http://example.com/authorize.php',
            [],
            'http://google.com/foo/bar?foo=fooVal#fragment',
            array('payum_token' => null)
        );

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertSame($gatewayName, $authorizeToken->getGatewayName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertSame(
            'http://example.com/authorize.php?payum_token='.$authorizeToken->getHash(),
            $authorizeToken->getTargetUrl()
        );
        $this->assertSame(
            'http://google.com/foo/bar?foo=fooVal#fragment',
            $authorizeToken->getAfterUrl()
        );
    }

    /**
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $registry
     *
     * @return AbstractTokenFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createTokenFactoryMock(StorageInterface $tokenStorage, StorageRegistryInterface $registry)
    {
        $factoryMock = $this->getMockForAbstractClass(AbstractTokenFactory::class, array($tokenStorage, $registry));
        $factoryMock
            ->method('generateUrl')
            ->willReturnCallback(function ($path, array $args) {
                return $path.'?'.http_build_query($args);
            })
        ;

        return $factoryMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->createMock(StorageInterface::class);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|StorageRegistryInterface
     */
    protected function createStorageRegistryMock()
    {
        return $this->createMock(StorageRegistryInterface::class);
    }
}
