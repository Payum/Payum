<?php
namespace Payum\Core\Tests\Security\Util;

use Payum\Core\Security\Util\Random;

class RandomTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldAllowGenerateToken()
    {
        $token = Random::generateToken();

        $this->assertInternalType('string', $token);
        $this->assertEquals(43, strlen($token));
    }
}
