<?php
namespace Payum\Tests\Bridge\Doctrine\Document;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfSecurityToken()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Doctrine\Document\Token');

        $this->assertTrue($rc->isSubclassOf('Payum\Model\Token'));
    }
}