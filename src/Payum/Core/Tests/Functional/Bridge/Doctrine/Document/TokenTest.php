<?php
namespace Payum\Core\Tests\Functional\Bridge\Doctrine\Document;

use Payum\Core\Model\Identity;
use Payum\Core\Tests\Functional\Bridge\Doctrine\MongoTest;
use Payum\Core\Tests\Mocks\Document\Token;

class TokenTest extends MongoTest
{
    /**
     * @test
     */
    public function shouldAllowPersist()
    {
        $token = new Token();
        $token->setTargetUrl('anUrl');
        $token->setGatewayName('aName');

        $this->dm->persist($token);
        $this->dm->flush();
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

        $this->dm->persist($token);
        $this->dm->flush();

        $hash = $token->getHash();

        $this->dm->clear();

        $foundToken = $this->dm->find(get_class($token), $hash);

        $this->assertNotSame($token, $foundToken);

        $this->assertEquals($token->getHash(), $foundToken->getHash());
        $this->assertEquals($token->getTargetUrl(), $foundToken->getTargetUrl());
        $this->assertEquals($token->getAfterUrl(), $foundToken->getAfterUrl());

        $this->assertNotSame($token->getDetails(), $foundToken->getDetails());
        $this->assertEquals($token->getDetails(), $foundToken->getDetails());
    }
}
