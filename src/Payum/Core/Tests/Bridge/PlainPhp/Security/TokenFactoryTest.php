<?php
namespace Payum\Core\Tests\Bridge\PlainPhp\Security;

use Payum\Core\Bridge\PlainPhp\Security\TokenFactory;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractTokenFactory;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Storage\StorageInterface;

class TokenFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsTokenFactoryInterface()
    {
        $rc = new \ReflectionClass(TokenFactory::class);

        $this->assertTrue($rc->implementsInterface(TokenFactoryInterface::class));
    }

    /**
     * @test
     */
    public function shouldBeSubClassOfAbtractTokenFactory()
    {
        $rc = new \ReflectionClass(TokenFactory::class);

        $this->assertTrue($rc->isSubclassOf(AbstractTokenFactory::class));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithExpectedArguments()
    {
        new TokenFactory(
            $this->createStorageMock(),
            $this->createStorageRegistryMock(),
            'http://example.com'
        );
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
            ->will($this->returnValue($token))
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
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
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
    public function shouldCreateTokenWithAfterUrl()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
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
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
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
    public function shouldCreateTokenForSecuredBaseUrl()
    {
        $token = new Token();
        $token->setHash('aHash');

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
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
    public function shouldCreateTokenForBaseUrlWithPath()
    {
        $token = new Token();
        $token->setHash('aHash');

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
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
     */
    public function shouldCreateTokenWithIdentityAsModel()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
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
    public function shouldCreateTokenWithoutModel()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
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
    public function shouldCreateTokenWithTargetPathAlreadyUrl()
    {
        $token = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($token))
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
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
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
    public function shouldNotOverwritePayumTokenHashInAfterUrl()
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
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
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
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
    public function shouldAllowCreateAfterUrlWithoutPayumToken()
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
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
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
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
    public function shouldAllowCreateAfterUrlWithFragment()
    {
        $authorizeToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
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
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageInterface
     */
    protected function createStorageMock()
    {
        return $this->getMock('Payum\Core\Storage\StorageInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StorageRegistryInterface
     */
    protected function createStorageRegistryMock()
    {
        return $this->getMock('Payum\Core\Registry\StorageRegistryInterface');
    }
}
