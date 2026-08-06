<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetToken;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

final class GetTokenTest extends TestCase
{
    public function testShouldAllowGetHashSetInConstructor(): void
    {
        $request = new GetToken('theHash');

        $this->assertSame('theHash', $request->getHash());
    }

    public function testShouldAllowGetPreviouslySetToken(): void
    {
        /** @var TokenInterface $token */
        $token = $this->createMock(TokenInterface::class);

        $request = new GetToken('aHash');
        $request->setToken($token);

        $this->assertSame($token, $request->getToken());
    }
}
