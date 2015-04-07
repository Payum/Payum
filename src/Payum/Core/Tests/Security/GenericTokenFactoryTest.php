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
        $gatewayName = 'theGatewayName';
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
                $gatewayName,
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
        $model = new \stdClass();
        $targetPath = 'theTargetPath';
        $targetParameters = array('target' => 'val');

        $token = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $gatewayName,
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
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
                $gatewayName,
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
                $gatewayName,
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
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
                $gatewayName,
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
                $gatewayName,
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
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
                $gatewayName,
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
                $gatewayName,
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
        $model = new \stdClass();
        $refundPath = 'theRefundPath';

        $refundToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $gatewayName,
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

        $actualToken = $factory->createRefundToken($gatewayName, $model);

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
        $gatewayName = 'theGatewayName';
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
            $gatewayName,
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
        $gatewayName = 'theGatewayName';
        $model = new \stdClass();
        $notifyPath = 'theNotifyPath';

        $notifyToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $gatewayName,
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

        $actualToken = $factory->createNotifyToken($gatewayName, $model);

        $this->assertSame($notifyToken, $actualToken);
    }

    /**
     * @test
     */
    public function shouldAllowCreateNotifyTokenWithoutModel()
    {
        $gatewayName = 'theGatewayName';
        $notifyPath = 'theNotifyPath';

        $notifyToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $gatewayName,
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

        $actualToken = $factory->createNotifyToken($gatewayName);

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
