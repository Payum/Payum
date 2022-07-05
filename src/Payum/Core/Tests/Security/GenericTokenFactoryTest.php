<?php

namespace Payum\Core\Tests\Security;

use Payum\Core\Model\Token;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenFactoryInterface;
use PHPUnit\Framework\TestCase;

class GenericTokenFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldImplementGenericTokenFactoryInterface()
    {
        $rc = new \ReflectionClass(GenericTokenFactory::class);

        $this->assertTrue($rc->implementsInterface(GenericTokenFactoryInterface::class));
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

    /**
     * @test
     */
    public function throwIfCapturePathNotConfigured()
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
            ->expects($this->atLeast(2))
            ->method('createToken')
            ->withConsecutive(
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $afterPath,
                    $afterParameters,
                    null,
                    []
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $capturePath,
                    [],
                    $afterUrl,
                    []
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $afterToken,
                $captureToken
            )
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
     */
    public function throwIfAuthorizePathNotConfigured()
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
            ->expects($this->atLeast(2))
            ->method('createToken')
            ->withConsecutive(
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $afterPath,
                    $afterParameters,
                    null,
                    []
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $authorizePath,
                    [],
                    $afterUrl,
                    []
                ]
            )
            ->willReturnOnConsecutiveCalls($afterToken, $authorizeToken)
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
     */
    public function throwIfRefundPathNotConfigured()
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
            ->expects($this->atLeast(2))
            ->method('createToken')
            ->withConsecutive(
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $afterPath,
                    $afterParameters,
                    null,
                    []
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $refundPath,
                    [],
                    $afterUrl,
                    []
                ]
            )
            ->willReturnOnConsecutiveCalls($afterToken, $refundToken)
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

    /**
     * @test
     */
    public function throwIfCancelPathNotConfigured()
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

    /**
     * @test
     */
    public function shouldAllowCreateCancelToken()
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
            ->expects($this->atLeast(2))
            ->method('createToken')
            ->withConsecutive(
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $afterPath,
                    $afterParameters,
                    null,
                    []
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $cancelPath,
                    [],
                    $afterUrl,
                    []
                ]
            )
            ->willReturn($afterToken, $cancelToken)
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

    /**
     * @test
     */
    public function shouldAllowCreateCancelTokenWithoutAfterPath()
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

    /**
     * @test
     */
    public function throwIfNotifyPathNotConfigured()
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

    /**
     * @test
     */
    public function throwIfPayoutPathNotConfigured()
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

    /**
     * @test
     */
    public function shouldAllowCreatePayoutToken()
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
            ->expects($this->atLeast(2))
            ->method('createToken')
            ->withConsecutive(
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $afterPath,
                    $afterParameters,
                    null,
                    []
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $payoutPath,
                    [],
                    $afterUrl,
                    []
                ]
            )
            ->willReturnOnConsecutiveCalls($afterToken, $payoutToken)
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
     * @return \PHPUnit\Framework\MockObject\MockObject|TokenFactoryInterface
     */
    protected function createTokenFactoryMock()
    {
        return $this->createMock(TokenFactoryInterface::class);
    }
}
