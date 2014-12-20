<?php
namespace Payum\Core\Tests\Bridge\Symfony\Security;

use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TokenFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementsGenericTokenFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Bridge\Symfony\Security\TokenFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\GenericTokenFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithExpectedArguments()
    {
        new TokenFactory(
            $this->createUrlGeneratorMock(),
            $this->createStorageMock(),
            $this->createStorageRegistryMock(),
            'capture.php',
            'notify.php',
            'authorize',
            'refund.php'
        );
    }

    /**
     * @test
     */
    public function shouldCreateCustomToken()
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
        $paymentName = 'thePaymentName';

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

        $routerMock = $this->createUrlGeneratorMock();
        $routerMock
            ->expects($this->at(0))
            ->method('generate')
            ->with('theTargetPath', array('targetPathKey' => 'targetPathVal', 'payum_token' => $token->getHash()), true)
            ->will($this->returnValue('theTargetUrl'))
        ;
        $routerMock
            ->expects($this->at(1))
            ->method('generate')
            ->with('theAfterPath', array('afterPathKey' => 'afterPathVal'), true)
            ->will($this->returnValue('theAfterUrl'))
        ;

        $factory = new TokenFactory(
            $routerMock,
            $tokenStorageMock,
            $storageRegistryMock,
            'capture',
            'notify',
            'authorize',
            'refund'
        );

        $actualToken = $factory->createToken(
            $paymentName,
            $model,
            'theTargetPath',
            array('targetPathKey' => 'targetPathVal'),
            'theAfterPath',
            array('afterPathKey' => 'afterPathVal')
        );

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals('theTargetUrl', $token->getTargetUrl());
        $this->assertEquals('theAfterUrl', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateCustomTokenWithoutAfterUrl()
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
        $paymentName = 'thePaymentName';

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

        $routerMock = $this->createUrlGeneratorMock();
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('theTargetPath', array('payum_token' => $token->getHash()), true)
            ->will($this->returnValue('theTargetUrl'))
        ;

        $factory = new TokenFactory(
            $routerMock,
            $tokenStorageMock,
            $storageRegistryMock,
            'capture',
            'notify',
            'authorize',
            'refund'
        );

        $actualToken = $factory->createToken($paymentName, $model, 'theTargetPath');

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals('theTargetUrl', $token->getTargetUrl());
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateNotifyToken()
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
        $paymentName = 'thePaymentName';

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

        $routerMock = $this->createUrlGeneratorMock();
        $routerMock
            ->expects($this->once())
            ->method('generate')
            ->with('notify', array('payum_token' => $token->getHash()), true)
            ->will($this->returnValue('theNotifyUrl'))
        ;

        $factory = new TokenFactory(
            $routerMock,
            $tokenStorageMock,
            $storageRegistryMock,
            'capture',
            'notify',
            'authorize',
            'refund'
        );

        $actualToken = $factory->createNotifyToken($paymentName, $model);

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identity, $token->getDetails());
        $this->assertEquals('theNotifyUrl', $token->getTargetUrl());
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateCaptureToken()
    {
        $captureToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($captureToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($captureToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($captureToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $routerMock = $this->createUrlGeneratorMock();
        $routerMock
            ->expects($this->at(0))
            ->method('generate')
            ->with('after', $this->isType('array'), true)
            ->will($this->returnValue('theAfterUrl'))
        ;
        $routerMock
            ->expects($this->at(1))
            ->method('generate')
            ->with('capture', array('payum_token' => $captureToken->getHash()), true)
            ->will($this->returnValue('theCaptureUrl'))
        ;

        $factory = new TokenFactory(
            $routerMock,
            $tokenStorageMock,
            $storageRegistryMock,
            'capture',
            'notify',
            'authorize',
            'refund'
        );

        $actualToken = $factory->createCaptureToken($paymentName, $model, 'after', array('afterKey' => 'afterVal'));

        $this->assertSame($captureToken, $actualToken);
        $this->assertEquals($paymentName, $captureToken->getPaymentName());
        $this->assertSame($identity, $captureToken->getDetails());
        $this->assertEquals('theCaptureUrl', $captureToken->getTargetUrl());
        $this->assertEquals('theAfterUrl', $captureToken->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateAuthorizeToken()
    {
        $authorizeToken = new Token();
        $afterToken = new Token();

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('create')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('update')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('create')
            ->will($this->returnValue($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('update')
            ->with($this->identicalTo($authorizeToken))
        ;

        $model = new \stdClass();
        $identity = new Identity('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('identify')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identity))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorage')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($modelStorage))
        ;

        $routerMock = $this->createUrlGeneratorMock();
        $routerMock
            ->expects($this->at(0))
            ->method('generate')
            ->with('after', $this->isType('array'), true)
            ->will($this->returnValue('theAfterUrl'))
        ;
        $routerMock
            ->expects($this->at(1))
            ->method('generate')
            ->with('authorize', array('payum_token' => $authorizeToken->getHash()), true)
            ->will($this->returnValue('theAuthorizeUrl'))
        ;

        $factory = new TokenFactory(
            $routerMock,
            $tokenStorageMock,
            $storageRegistryMock,
            'capture',
            'notify',
            'authorize',
            'refund'
        );

        $actualToken = $factory->createAuthorizeToken($paymentName, $model, 'after', array('afterKey' => 'afterVal'));

        $this->assertSame($authorizeToken, $actualToken);
        $this->assertEquals($paymentName, $authorizeToken->getPaymentName());
        $this->assertSame($identity, $authorizeToken->getDetails());
        $this->assertEquals('theAuthorizeUrl', $authorizeToken->getTargetUrl());
        $this->assertEquals('theAfterUrl', $authorizeToken->getAfterUrl());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|UrlGeneratorInterface
     */
    protected function createUrlGeneratorMock()
    {
        return $this->getMock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
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
