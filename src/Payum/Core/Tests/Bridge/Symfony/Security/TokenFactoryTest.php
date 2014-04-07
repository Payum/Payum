<?php
namespace Payum\Core\Tests\Bridge\Symfony\Security;

use Payum\Core\Bridge\Symfony\Security\TokenFactory;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Model\Identificator;
use Payum\Core\Model\Token;
use Payum\Core\PaymentInterface;
use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\RouterInterface;

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
            $this->createRouterMock(),
            $this->createStorageMock(),
            $this->createStorageRegistryMock(),
            'capture.php',
            'notify.php'
        );
    }

    /**
     * @test
     */
    public function shouldCreateCustomToken()
    {
        $token = new Token;

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('createModel')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('updateModel')
            ->with($this->identicalTo($token))
        ;

        $model = new \stdClass;
        $identificator = new Identificator('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('getIdentificator')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identificator))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorageForClass')
            ->with($this->identicalTo($model), $paymentName)
            ->will($this->returnValue($modelStorage))
        ;

        $routerMock = $this->createRouterMock();
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
            'notify'
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
        $this->assertSame($identificator, $token->getDetails());
        $this->assertEquals('theTargetUrl', $token->getTargetUrl());
        $this->assertEquals('theAfterUrl', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateCustomTokenWithoutAfterUrl()
    {
        $token = new Token;

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('createModel')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('updateModel')
            ->with($this->identicalTo($token))
        ;

        $model = new \stdClass;
        $identificator = new Identificator('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('getIdentificator')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identificator))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorageForClass')
            ->with($this->identicalTo($model), $paymentName)
            ->will($this->returnValue($modelStorage))
        ;

        $routerMock = $this->createRouterMock();
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
            'capture.php',
            'notify.php'
        );

        $actualToken = $factory->createToken($paymentName, $model, 'theTargetPath');

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identificator, $token->getDetails());
        $this->assertEquals('theTargetUrl', $token->getTargetUrl());
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateNotifyToken()
    {
        $token = new Token;

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->once())
            ->method('createModel')
            ->will($this->returnValue($token))
        ;
        $tokenStorageMock
            ->expects($this->once())
            ->method('updateModel')
            ->with($this->identicalTo($token))
        ;

        $model = new \stdClass;
        $identificator = new Identificator('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->once())
            ->method('getIdentificator')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identificator))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->once())
            ->method('getStorageForClass')
            ->with($this->identicalTo($model), $paymentName)
            ->will($this->returnValue($modelStorage))
        ;

        $routerMock = $this->createRouterMock();
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
            'notify'
        );

        $actualToken = $factory->createNotifyToken($paymentName, $model);

        $this->assertSame($token, $actualToken);
        $this->assertEquals($paymentName, $token->getPaymentName());
        $this->assertSame($identificator, $token->getDetails());
        $this->assertEquals('theNotifyUrl', $token->getTargetUrl());
        $this->assertNull($token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldCreateCaptureToken()
    {
        $captureToken = new Token;
        $afterToken = new Token;

        $tokenStorageMock = $this->createStorageMock();
        $tokenStorageMock
            ->expects($this->at(0))
            ->method('createModel')
            ->will($this->returnValue($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(1))
            ->method('updateModel')
            ->with($this->identicalTo($afterToken))
        ;
        $tokenStorageMock
            ->expects($this->at(2))
            ->method('createModel')
            ->will($this->returnValue($captureToken))
        ;
        $tokenStorageMock
            ->expects($this->at(3))
            ->method('updateModel')
            ->with($this->identicalTo($captureToken))
        ;
        $tokenStorageMock
            ->expects($this->at(4))
            ->method('updateModel')
            ->with($this->identicalTo($captureToken))
        ;


        $model = new \stdClass;
        $identificator = new Identificator('anId', 'stdClass');
        $paymentName = 'thePaymentName';

        $modelStorage = $this->createStorageMock();
        $modelStorage
            ->expects($this->exactly(2))
            ->method('getIdentificator')
            ->with($this->identicalTo($model))
            ->will($this->returnValue($identificator))
        ;

        $storageRegistryMock = $this->createStorageRegistryMock();
        $storageRegistryMock
            ->expects($this->exactly(2))
            ->method('getStorageForClass')
            ->with($this->identicalTo($model), $paymentName)
            ->will($this->returnValue($modelStorage))
        ;

        $routerMock = $this->createRouterMock();
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
            'notify'
        );

        $actualToken = $factory->createCaptureToken($paymentName, $model, 'after', array('afterKey' => 'afterVal'));

        $this->assertSame($captureToken, $actualToken);
        $this->assertEquals($paymentName, $captureToken->getPaymentName());
        $this->assertSame($identificator, $captureToken->getDetails());
        $this->assertEquals('theCaptureUrl', $captureToken->getTargetUrl());
        $this->assertEquals('theAfterUrl', $captureToken->getAfterUrl());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    protected function createRouterMock()
    {
        return $this->getMock('Symfony\Component\Routing\RouterInterface');
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
