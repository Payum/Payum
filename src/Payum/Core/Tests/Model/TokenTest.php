<?php

namespace Payum\Core\Tests\Model;

use Payum\Core\Model\Identity;
use Payum\Core\Model\Token;
use Payum\Core\Security\TokenInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

class TokenTest extends TestCase
{
    public function testShouldExtendDetailsAwareInterface(): void
    {
        $rc = new ReflectionClass(Token::class);

        $this->assertTrue($rc->implementsInterface(TokenInterface::class));
    }

    public function testShouldAllowGetHashGeneratedInConstructor(): void
    {
        $token = new Token();

        $this->assertNotEmpty($token->getHash());
    }

    public function testShouldGenerateDifferentTokensInConstructor(): void
    {
        $tokenOne = new Token();
        $tokenTwo = new Token();

        $this->assertNotSame($tokenOne->getHash(), $tokenTwo->getHash());
    }

    public function testShouldAllowGetPreviouslySetHash(): void
    {
        $token = new Token();

        $token->setHash('theToken');

        $this->assertSame('theToken', $token->getHash());
    }

    public function testShouldAllowGetPreviouslySetGatewayName(): void
    {
        $token = new Token();

        $token->setGatewayName('theName');

        $this->assertSame('theName', $token->getGatewayName());
    }

    public function testShouldAllowGetPreviouslySetTargetUrl(): void
    {
        $token = new Token();

        $token->setTargetUrl('theUrl');

        $this->assertSame('theUrl', $token->getTargetUrl());
    }

    public function testShouldAllowGetPreviouslySetAfterUrl(): void
    {
        $token = new Token();

        $token->setAfterUrl('theUrl');

        $this->assertSame('theUrl', $token->getAfterUrl());
    }

    public function testShouldAllowGetPreviouslySetDetails(): void
    {
        $expectedIdentity = new Identity('anId', stdClass::class);

        $token = new Token();

        $token->setDetails($expectedIdentity);

        $this->assertSame($expectedIdentity, $token->getDetails());
    }

    public function testShouldAllowGetIdentityPreviouslySetAsDetails(): void
    {
        $expectedIdentity = new Identity('anId', stdClass::class);

        $token = new Token();

        $token->setDetails($expectedIdentity);

        $this->assertSame($expectedIdentity, $token->getDetails());
    }
}
