<?php
namespace Payum\Paypal\Masspay\Nvp\Tests\Request\Api;

use Payum\Core\Request\Generic;
use Payum\Paypal\Masspay\Nvp\Request\Api\Masspay;

class MasspayTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(Masspay::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }
}
