<?php
namespace Payum\Core\Tests\Request;

use Payum\Core\Request\GetToken;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

class GetTokenTest extends TestCase
{
    /**
     * @test
     */
    public function couldBeConstructedWithHashAsArgument()
    {
        new GetToken('aHash');
    }

    /**
     * @test
     */
    public function shouldAllowGetHashSetInConstructor()
    {
        $request = new GetToken('theHash');

        $this->assertSame('theHash', $request->getHash());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetToken()
    {
        /** @var TokenInterface $token */
        $token = $this->createMock(TokenInterface::class);

        $request = new GetToken('aHash');
        $request->setToken($token);

        $this->assertSame($token, $request->getToken());
    }
}
