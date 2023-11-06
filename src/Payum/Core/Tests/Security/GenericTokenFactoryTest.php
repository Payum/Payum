<?php
namespace Payum\Core\Tests\Security;

use Payum\Core\Model\Token;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenFactoryInterface;
use PHPUnit\Framework\TestCase;

class GenericTokenFactoryTest extends TestCase
{
    public function testShouldImplementGenericTokenFactoryInterface()
    {
        $rc = new \ReflectionClass(GenericTokenFactory::class);

        $this->assertTrue($rc->implementsInterface(GenericTokenFactoryInterface::class));
    }

    public function testShouldAllowCreateCustomTokenWithAfterPath()
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


        $factory = new GenericTokenFactory($tokenFactoryMock, []);

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

    public function testShouldAllowCreateCustomTokenWithoutAfterPath()
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
                []
            )
            ->willReturn($token)
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, []);

        $actualToken = $factory->createToken(
            $gatewayName,
            $model,
            $targetPath,
            $targetParameters
        );

        $this->assertSame($token, $actualToken);
    }

    public function testThrowIfCapturePathNotConfigured()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The path "capture" is not found. Possible paths are foo, bar');
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

    public function testShouldAllowCreateCaptureToken()
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
                []
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
                [],
                $afterUrl,
                []
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

    public function testThrowIfAuthorizePathNotConfigured()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The path "authorize" is not found. Possible paths are foo, bar');
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

    public function testShouldAllowCreateAuthorizeToken()
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
                []
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
                [],
                $afterUrl,
                []
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

    public function testThrowIfRefundPathNotConfigured()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The path "refund" is not found. Possible paths are foo, bar');
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

    public function testShouldAllowCreateRefundToken()
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
                []
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
                [],
                $afterUrl,
                []
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

    public function testShouldAllowCreateRefundTokenWithoutAfterPath()
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
                [],
                null,
                []
            )
            ->willReturn($refundToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'refund' => $refundPath
        ));

        $actualToken = $factory->createRefundToken($gatewayName, $model);

        $this->assertSame($refundToken, $actualToken);
    }

    public function testThrowIfCancelPathNotConfigured()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The path "cancel" is not found. Possible paths are foo, bar');
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

        $factory->createCancelToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    public function testShouldAllowCreateCancelToken()
    {
        $gatewayName = 'theGatewayName';
        $model = new \stdClass();
        $cancelPath = 'theCancelPath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = array('after' => 'val');

        $afterToken = new Token();
        $afterToken->setTargetUrl($afterUrl);

        $cancelToken = new Token();

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
                []
            )
            ->willReturn($afterToken)
        ;
        $tokenFactoryMock
            ->expects($this->at(1))
            ->method('createToken')
            ->with(
                $gatewayName,
                $this->identicalTo($model),
                $cancelPath,
                [],
                $afterUrl,
                []
            )
            ->willReturn($cancelToken)
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'cancel' => $cancelPath
        ));

        $actualToken = $factory->createCancelToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($cancelToken, $actualToken);
    }

    public function testShouldAllowCreateCancelTokenWithoutAfterPath()
    {
        $gatewayName = 'theGatewayName';
        $model = new \stdClass();
        $cancelPath = 'theCancelPath';

        $cancelToken = new Token();

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->once())
            ->method('createToken')
            ->with(
                $gatewayName,
                $this->identicalTo($model),
                $cancelPath,
                [],
                null,
                []
            )
            ->willReturn($cancelToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'cancel' => $cancelPath
        ));

        $actualToken = $factory->createCancelToken($gatewayName, $model);

        $this->assertSame($cancelToken, $actualToken);
    }

    public function testThrowIfNotifyPathNotConfigured()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The path "notify" is not found. Possible paths are foo, bar');
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

    public function testShouldAllowCreateNotifyToken()
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
                [],
                null,
                []
            )
            ->willReturn($notifyToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'notify' => $notifyPath
        ));

        $actualToken = $factory->createNotifyToken($gatewayName, $model);

        $this->assertSame($notifyToken, $actualToken);
    }

    public function testShouldAllowCreateNotifyTokenWithoutModel()
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
                [],
                null,
                []
            )
            ->willReturn($notifyToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'notify' => $notifyPath
        ));

        $actualToken = $factory->createNotifyToken($gatewayName);

        $this->assertSame($notifyToken, $actualToken);
    }

    public function testThrowIfPayoutPathNotConfigured()
    {
        $this->expectException(\Payum\Core\Exception\LogicException::class);
        $this->expectExceptionMessage('The path "payout" is not found. Possible paths are foo, bar');
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

        $factory->createPayoutToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    public function testShouldAllowCreatePayoutToken()
    {
        $gatewayName = 'theGatewayName';
        $model = new \stdClass();
        $payoutPath = 'thePayoutPath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = array('after' => 'val');

        $afterToken = new Token();
        $afterToken->setTargetUrl($afterUrl);

        $payoutToken = new Token();

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
                []
            )
            ->willReturn($afterToken)
        ;
        $tokenFactoryMock
            ->expects($this->at(1))
            ->method('createToken')
            ->with(
                $gatewayName,
                $this->identicalTo($model),
                $payoutPath,
                [],
                $afterUrl,
                []
            )
            ->willReturn($payoutToken)
        ;


        $factory = new GenericTokenFactory($tokenFactoryMock, array(
            'payout' => $payoutPath
        ));

        $actualToken = $factory->createPayoutToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($payoutToken, $actualToken);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TokenFactoryInterface
     */
    protected function createTokenFactoryMock()
    {
        return $this->createMock(TokenFactoryInterface::class);
    }
}
