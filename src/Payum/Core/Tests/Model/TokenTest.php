<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Token;
use Payum\Core\Model\Identity;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass(Token::class);

        $this->assertTrue($rc->implementsInterface(TokenInterface::class));
    }

    /**
     * @test
     */
    public function shouldAllowGetHashGeneratedInConstructor()
    {
        $token = new Token();

        $this->assertNotEmpty($token->getHash());
    }

    /**
     * @test
     */
    public function shouldGenerateDifferentTokensInConstructor()
    {
        $tokenOne = new Token();
        $tokenTwo = new Token();

        $this->assertNotEquals($tokenOne->getHash(), $tokenTwo->getHash());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetHash()
    {
        $token = new Token();

        $token->setHash('theToken');

        $this->assertSame('theToken', $token->getHash());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetGatewayName()
    {
        $token = new Token();

        $token->setGatewayName('theName');

        $this->assertSame('theName', $token->getGatewayName());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetTargetUrl()
    {
        $token = new Token();

        $token->setTargetUrl('theUrl');

        $this->assertSame('theUrl', $token->getTargetUrl());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetAfterUrl()
    {
        $token = new Token();

        $token->setAfterUrl('theUrl');

        $this->assertSame('theUrl', $token->getAfterUrl());
    }

    /**
     * @test
     */
    public function shouldAllowGetPreviouslySetDetails()
    {
        $expectedIdentity = new Identity('anId', 'stdClass');

        $token = new Token();

        $token->setDetails($expectedIdentity);

        $this->assertSame($expectedIdentity, $token->getDetails());
    }

    /**
     * @test
     */
    public function shouldAllowGetIdentityPreviouslySetAsDetails()
    {
        $expectedIdentity = new Identity('anId', 'stdClass');

        $token = new Token();

        $token->setDetails($expectedIdentity);

        $this->assertSame($expectedIdentity, $token->getDetails());
    }
}
