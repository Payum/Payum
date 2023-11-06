<?php
namespace Payum\Core\Tests\Security\Util;

use Payum\Core\Security\Util\Random;
use PHPUnit\Framework\TestCase;

class RandomTest extends TestCase
{
    public function testShouldAllowGenerateToken()
    {
        $token = Random::generateToken();

        $this->assertIsString($token);
        $this->assertSame(43, strlen($token));
    }
}
