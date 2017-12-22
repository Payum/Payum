<?php
namespace Payum\Core\Tests\Security\Util;

use Payum\Core\Security\Util\Random;
use PHPUnit\Framework\TestCase;

class RandomTest extends TestCase
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
