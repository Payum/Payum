<?php
namespace Payum\Stripe\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Stripe\Request\Api\ObtainToken;

class ObtainTokenTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(ObtainToken::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
