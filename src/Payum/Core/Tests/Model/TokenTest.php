<?php
namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Token;
use Payum\Core\Model\Identity;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testShouldExtendDetailsAwareInterface()
    {
        $rc = new \ReflectionClass(Token::class);

        $this->assertTrue($rc->implementsInterface(TokenInterface::class));
    }

    public function testShouldAllowGetHashGeneratedInConstructor()
    {
        $token = new Token();

        $this->assertNotEmpty($token->getHash());
    }

    public function testShouldGenerateDifferentTokensInConstructor()
    {
        $tokenOne = new Token();
        $tokenTwo = new Token();

        $this->assertNotSame($tokenOne->getHash(), $tokenTwo->getHash());
    }

    public function testShouldAllowGetPreviouslySetHash()
    {
        $token = new Token();

        $token->setHash('theToken');

        $this->assertSame('theToken', $token->getHash());
    }

    public function testShouldAllowGetPreviouslySetGatewayName()
    {
        $token = new Token();

        $token->setGatewayName('theName');

        $this->assertSame('theName', $token->getGatewayName());
    }

    public function testShouldAllowGetPreviouslySetTargetUrl()
    {
        $token = new Token();

        $token->setTargetUrl('theUrl');

        $this->assertSame('theUrl', $token->getTargetUrl());
    }

    public function testShouldAllowGetPreviouslySetAfterUrl()
    {
        $token = new Token();

        $token->setAfterUrl('theUrl');

        $this->assertSame('theUrl', $token->getAfterUrl());
    }

    public function testShouldAllowGetPreviouslySetDetails()
    {
        $expectedIdentity = new Identity('anId', 'stdClass');

        $token = new Token();

        $token->setDetails($expectedIdentity);

        $this->assertSame($expectedIdentity, $token->getDetails());
    }

    public function testShouldAllowGetIdentityPreviouslySetAsDetails()
    {
        $expectedIdentity = new Identity('anId', 'stdClass');

        $token = new Token();

        $token->setDetails($expectedIdentity);

        $this->assertSame($expectedIdentity, $token->getDetails());
    }
}
