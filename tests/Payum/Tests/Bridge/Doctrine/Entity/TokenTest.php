<?php
namespace Payum\Tests\Bridge\Doctrine\Entity;

class TokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfSecurityToken()
    {
        $rc = new \ReflectionClass('Payum\Bridge\Doctrine\Entity\Token');

        $this->assertTrue($rc->isSubclassOf('Payum\Model\Token'));
    }
}