<?php
namespace Payum\Core\Tests\Security;

use Payum\Core\Model\Token;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\TokenFactoryInterface;

class GenericTokenFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldImplementGenericTokenFactoryInterface()
    {
        $rc = new \ReflectionClass('Payum\Core\Security\GenericTokenFactory');

        $this->assertTrue($rc->implementsInterface('Payum\Core\Security\GenericTokenFactoryInterface'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithTokenFactoryAndPaths()
    {
        new GenericTokenFactory($this->createTokenFactoryMock(), array());
    }

    /**
     * @test
     */
    public function shouldAllowCreateCustomTokenWithAfterPath()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $targetPath = 'theTargetPath';
        $targetParameters = array('target' => 'val');
        $afterPath = 'theAfterPath';
        $afterParameters = array('after' => 'val');

        $token = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $targetPath,
                $targetParameters,
                $afterPath,
                $afterParameters
            )
            ->willReturn($token)
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array());

        $actualToken = $factory->createToken(
            $paymentName,
            $model,
            $targetPath,
            $targetParameters,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($token, $actualToken);
    }

    /**
     * @test
     */
    public function shouldAllowCreateCustomTokenWithoutAfterPath()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $targetPath = 'theTargetPath';
        $targetParameters = array('target' => 'val');

        $token = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $targetPath,
                $targetParameters,
                null,
                array()
            )
            ->willReturn($token)
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array());

        $actualToken = $factory->createToken(
            $paymentName,
            $model,
            $targetPath,
            $targetParameters
        );

        $this->assertSame($token, $actualToken);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The path "capture" is not found. Possible paths are foo, bar
     */
    public function throwIfCapturePathNotConfigured()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = array('after' => 'val');

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array('foo' => 'fooPath', 'bar' => 'barPath'));

        $factory->createCaptureToken(
            $paymentName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateCaptureToken()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $capturePath = 'theCapturePath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = array('after' => 'val');

        $afterToken = new Token();
        $afterToken->setTargetUrl($afterUrl);

        $captureToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->at(0))
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $afterPath,
                $afterParameters,
                null,
                array()
            )
            ->willReturn($afterToken)
        ;
        $tokenFactoryMock
            ->expects($this->at(1))
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $capturePath,
                array(),
                $afterUrl,
                array()
            )
            ->willReturn($captureToken)
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'capture' => $capturePath
        ));

        $actualToken = $factory->createCaptureToken(
            $paymentName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($captureToken, $actualToken);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The path "authorize" is not found. Possible paths are foo, bar
     */
    public function throwIfAuthorizePathNotConfigured()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = array('after' => 'val');

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array('foo' => 'fooPath', 'bar' => 'barPath'));

        $factory->createAuthorizeToken(
            $paymentName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateAuthorizeToken()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $authorizePath = 'theAuthorizePath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = array('after' => 'val');

        $afterToken = new Token();
        $afterToken->setTargetUrl($afterUrl);

        $authorizeToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->at(0))
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $afterPath,
                $afterParameters,
                null,
                array()
            )
            ->willReturn($afterToken)
        ;
        $tokenFactoryMock
            ->expects($this->at(1))
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $authorizePath,
                array(),
                $afterUrl,
                array()
            )
            ->willReturn($authorizeToken)
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'authorize' => $authorizePath
        ));

        $actualToken = $factory->createAuthorizeToken(
            $paymentName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($authorizeToken, $actualToken);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The path "refund" is not found. Possible paths are foo, bar
     */
    public function throwIfRefundPathNotConfigured()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = array('after' => 'val');

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array('foo' => 'fooPath', 'bar' => 'barPath'));

        $factory->createRefundToken(
            $paymentName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateRefundToken()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $refundPath = 'theRefundPath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = array('after' => 'val');

        $afterToken = new Token();
        $afterToken->setTargetUrl($afterUrl);

        $refundToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->at(0))
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $afterPath,
                $afterParameters,
                null,
                array()
            )
            ->willReturn($afterToken)
        ;
        $tokenFactoryMock
            ->expects($this->at(1))
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $refundPath,
                array(),
                $afterUrl,
                array()
            )
            ->willReturn($refundToken)
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'refund' => $refundPath
        ));

        $actualToken = $factory->createRefundToken(
            $paymentName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($refundToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldAllowCreateRefundTokenWithoutAfterPath()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $refundPath = 'theRefundPath';

        $refundToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $refundPath,
                array(),
                null,
                array()
            )
            ->willReturn($refundToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'refund' => $refundPath
        ));

        $actualToken = $factory->createRefundToken($paymentName, $model);

        $this->assertSame($refundToken, $actualToken);
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\LogicException
     * @expectedExceptionMessage The path "notify" is not found. Possible paths are foo, bar
     */
    public function throwIfNotifyPathNotConfigured()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = array('after' => 'val');

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array('foo' => 'fooPath', 'bar' => 'barPath'));

        $factory->createNotifyToken(
            $paymentName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    /**
     * @test
     */
    public function shouldAllowCreateNotifyToken()
    {
        $paymentName = 'thePaymentName';
        $model = new \stdClass();
        $notifyPath = 'theNotifyPath';

        $notifyToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $paymentName,
                $this->identicalTo($model),
                $notifyPath,
                array(),
                null,
                array()
            )
            ->willReturn($notifyToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'notify' => $notifyPath
        ));

        $actualToken = $factory->createNotifyToken($paymentName, $model);

        $this->assertSame($notifyToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldAllowCreateNotifyTokenWithoutModel()
    {
        $paymentName = 'thePaymentName';
        $notifyPath = 'theNotifyPath';

        $notifyToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $paymentName,
                null,
                $notifyPath,
                array(),
                null,
                array()
            )
            ->willReturn($notifyToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'notify' => $notifyPath
        ));

        $actualToken = $factory->createNotifyToken($paymentName);

        $this->assertSame($notifyToken, $actualToken);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TokenFactoryInterface
     */
    protected function createTokenFactoryMock()
    {
        return $this->getMock('Payum\Core\Security\TokenFactoryInterface');
    }
}
