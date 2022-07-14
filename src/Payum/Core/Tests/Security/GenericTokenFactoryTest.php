<?php

namespace Payum\Core\Tests\Security;

use Payum\Core\Exception\LogicException;
use Payum\Core\Model\Token;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class GenericTokenFactoryTest extends TestCase
{
    public function testShouldImplementGenericTokenFactoryInterface(): void
    {
        $rc = new ReflectionClass(GenericTokenFactory::class);

        $this->assertTrue($rc->implementsInterface(GenericTokenFactoryInterface::class));
    }

    public function testShouldAllowCreateCustomTokenWithAfterPath(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $targetPath = 'theTargetPath';
        $targetParameters = [
            'target' => 'val',
        ];
        $afterPath = 'theAfterPath';
        $afterParameters = [
            'after' => 'val',
        ];

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

    public function testShouldAllowCreateCustomTokenWithoutAfterPath(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $targetPath = 'theTargetPath';
        $targetParameters = [
            'target' => 'val',
        ];

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

    public function testThrowIfCapturePathNotConfigured(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The path "capture" is not found. Possible paths are foo, bar');
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = [
            'after' => 'val',
        ];

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'foo' => 'fooPath',
            'bar' => 'barPath',
        ]);

        $factory->createCaptureToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    public function testShouldAllowCreateCaptureToken(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $capturePath = 'theCapturePath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = [
            'after' => 'val',
        ];

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
                    [],
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $capturePath,
                    [],
                    $afterUrl,
                    [],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $afterToken,
                $captureToken
            )
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'capture' => $capturePath,
        ]);

        $actualToken = $factory->createCaptureToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($captureToken, $actualToken);
    }

    public function testThrowIfAuthorizePathNotConfigured(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The path "authorize" is not found. Possible paths are foo, bar');
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = [
            'after' => 'val',
        ];

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'foo' => 'fooPath',
            'bar' => 'barPath',
        ]);

        $factory->createAuthorizeToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    public function testShouldAllowCreateAuthorizeToken(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $authorizePath = 'theAuthorizePath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = [
            'after' => 'val',
        ];

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
                    [],
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $authorizePath,
                    [],
                    $afterUrl,
                    [],
                ]
            )
            ->willReturnOnConsecutiveCalls($afterToken, $authorizeToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'authorize' => $authorizePath,
        ]);

        $actualToken = $factory->createAuthorizeToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($authorizeToken, $actualToken);
    }

    public function testThrowIfRefundPathNotConfigured(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The path "refund" is not found. Possible paths are foo, bar');
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = [
            'after' => 'val',
        ];

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'foo' => 'fooPath',
            'bar' => 'barPath',
        ]);

        $factory->createRefundToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    public function testShouldAllowCreateRefundToken(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $refundPath = 'theRefundPath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = [
            'after' => 'val',
        ];

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
                    [],
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $refundPath,
                    [],
                    $afterUrl,
                    [],
                ]
            )
            ->willReturnOnConsecutiveCalls($afterToken, $refundToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'refund' => $refundPath,
        ]);

        $actualToken = $factory->createRefundToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($refundToken, $actualToken);
    }

    public function testShouldAllowCreateRefundTokenWithoutAfterPath(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
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

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'refund' => $refundPath,
        ]);

        $actualToken = $factory->createRefundToken($gatewayName, $model);

        $this->assertSame($refundToken, $actualToken);
    }

    public function testThrowIfCancelPathNotConfigured(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The path "cancel" is not found. Possible paths are foo, bar');
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = [
            'after' => 'val',
        ];

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'foo' => 'fooPath',
            'bar' => 'barPath',
        ]);

        $factory->createCancelToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    public function testShouldAllowCreateCancelToken(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $cancelPath = 'theCancelPath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = [
            'after' => 'val',
        ];

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
                    [],
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $cancelPath,
                    [],
                    $afterUrl,
                    [],
                ]
            )
            ->willReturn($afterToken, $cancelToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'cancel' => $cancelPath,
        ]);

        $actualToken = $factory->createCancelToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($cancelToken, $actualToken);
    }

    public function testShouldAllowCreateCancelTokenWithoutAfterPath(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
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

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'cancel' => $cancelPath,
        ]);

        $actualToken = $factory->createCancelToken($gatewayName, $model);

        $this->assertSame($cancelToken, $actualToken);
    }

    public function testThrowIfNotifyPathNotConfigured(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The path "notify" is not found. Possible paths are foo, bar');
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = [
            'after' => 'val',
        ];

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'foo' => 'fooPath',
            'bar' => 'barPath',
        ]);

        $factory->createNotifyToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    public function testShouldAllowCreateNotifyToken(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
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

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'notify' => $notifyPath,
        ]);

        $actualToken = $factory->createNotifyToken($gatewayName, $model);

        $this->assertSame($notifyToken, $actualToken);
    }

    public function testShouldAllowCreateNotifyTokenWithoutModel(): void
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

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'notify' => $notifyPath,
        ]);

        $actualToken = $factory->createNotifyToken($gatewayName);

        $this->assertSame($notifyToken, $actualToken);
    }

    public function testThrowIfPayoutPathNotConfigured(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The path "payout" is not found. Possible paths are foo, bar');
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $afterPath = 'theAfterPath';
        $afterParameters = [
            'after' => 'val',
        ];

        $tokenFactoryMock = $this->createTokenFactoryMock();
        $tokenFactoryMock
            ->expects($this->never())
            ->method('createToken')
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'foo' => 'fooPath',
            'bar' => 'barPath',
        ]);

        $factory->createPayoutToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );
    }

    public function testShouldAllowCreatePayoutToken(): void
    {
        $gatewayName = 'theGatewayName';
        $model = new stdClass();
        $payoutPath = 'thePayoutPath';
        $afterPath = 'theAfterPath';
        $afterUrl = 'theAfterUrl';
        $afterParameters = [
            'after' => 'val',
        ];

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
                    [],
                ],
                [
                    $gatewayName,
                    $this->identicalTo($model),
                    $payoutPath,
                    [],
                    $afterUrl,
                    [],
                ]
            )
            ->willReturnOnConsecutiveCalls($afterToken, $payoutToken)
        ;

        $factory = new GenericTokenFactory($tokenFactoryMock, [
            'payout' => $payoutPath,
        ]);

        $actualToken = $factory->createPayoutToken(
            $gatewayName,
            $model,
            $afterPath,
            $afterParameters
        );

        $this->assertSame($payoutToken, $actualToken);
    }

    /**
     * @return MockObject|TokenFactoryInterface
     */
    protected function createTokenFactoryMock()
    {
        return $this->createMock(TokenFactoryInterface::class);
    }
}
