<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Legacy;

use Payum\Core\Legacy\GenericTokenFactoryIssuer;
use Payum\Core\Security\GenericTokenFactory;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Security\TokenFactoryInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Security\TokenPurpose;
use PHPUnit\Framework\TestCase;
use stdClass;

final class GenericTokenFactoryIssuerTest extends TestCase
{
    public function testShouldHandBackAFactoryThatIsAlreadyAnIssuer(): void
    {
        $factory = new GenericTokenFactory($this->createMock(TokenFactoryInterface::class), []);

        $this->assertSame($factory, GenericTokenFactoryIssuer::wrap($factory));
    }

    /**
     * @return iterable<string, array{TokenPurpose, string, list<mixed>}>
     */
    public static function providePurposes(): iterable
    {
        $model = new stdClass();
        $after = [
            'a' => 'b',
        ];

        yield 'authorize' => [TokenPurpose::Authorize, 'createAuthorizeToken', ['aGateway', $model, 'after.php', $after]];
        yield 'capture' => [TokenPurpose::Capture, 'createCaptureToken', ['aGateway', $model, 'after.php', $after]];
        yield 'refund' => [TokenPurpose::Refund, 'createRefundToken', ['aGateway', $model, 'after.php', $after]];
        yield 'payout' => [TokenPurpose::Payout, 'createPayoutToken', ['aGateway', $model, 'after.php', $after]];
        yield 'notify' => [TokenPurpose::Notify, 'createNotifyToken', ['aGateway', $model]];
    }

    /**
     * @dataProvider providePurposes
     *
     * @param list<mixed> $arguments
     */
    public function testShouldCallTheMethodThatMeansThePurpose(TokenPurpose $purpose, string $method, array $arguments): void
    {
        $token = $this->createMock(TokenInterface::class);

        $factory = $this->createMock(GenericTokenFactoryInterface::class);
        $factory->expects($this->once())
            ->method($method)
            ->with(...$arguments)
            ->willReturn($token);

        $issuer = GenericTokenFactoryIssuer::wrap($factory);

        $this->assertSame($token, $issuer->create($purpose, 'aGateway', $arguments[1], 'after.php', [
            'a' => 'b',
        ]));
    }
}
