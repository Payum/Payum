<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Entity;

use Doctrine\ORM\Tools\SchemaValidator;
use Payum\Core\Model\Identity;
use Payum\Core\Tests\Functional\Bridge\Doctrine\OrmTest;
use Payum\Core\Tests\Mocks\Entity\Token;

class TokenTest extends OrmTest
{
    /**
     * @test
     */
    public function shouldAllSchemasBeValid()
    {
        $schemaValidator = new SchemaValidator($this->em);

        $this->assertEmpty($schemaValidator->validateMapping());
    }

    /**
     * @test
     */
    public function shouldAllowPersist()
    {
        $token = new Token();
        $token->setTargetUrl('anUrl');
        $token->setGatewayName('aName');

        $this->em->persist($token);
        $this->em->flush();
    }

    /**
     * @test
     */
    public function shouldAllowFindPersistedToken()
    {
        $token = new Token();
        $token->setTargetUrl('anUrl');
        $token->setGatewayName('aName');
        $token->setAfterUrl('anAfterUrl');
        $token->setDetails(new Identity('anId', 'stdClass'));

        $this->em->persist($token);
        $this->em->flush();

        $hash = $token->getHash();

        $this->em->clear();

        $foundToken = $this->em->find(get_class($token), $hash);

        $this->assertNotSame($token, $foundToken);

        $this->assertEquals($token->getHash(), $foundToken->getHash());
        $this->assertEquals($token->getTargetUrl(), $foundToken->getTargetUrl());
        $this->assertEquals($token->getAfterUrl(), $foundToken->getAfterUrl());

        $this->assertNotSame($token->getDetails(), $foundToken->getDetails());
        $this->assertEquals($token->getDetails(), $foundToken->getDetails());
    }
}
