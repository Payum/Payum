<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetToken;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

class GetTokenTest extends TestCase
{
    public function testShouldAllowGetHashSetInConstructor()
    {
        $request = new GetToken('theHash');

        $this->assertSame('theHash', $request->getHash());
    }

    public function testShouldAllowGetPreviouslySetToken()
    {
        /** @var TokenInterface $token */
        $token = $this->createMock(TokenInterface::class);

        $request = new GetToken('aHash');
        $request->setToken($token);

        $this->assertSame($token, $request->getToken());
    }
}
