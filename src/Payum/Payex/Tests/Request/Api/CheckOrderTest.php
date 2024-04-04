<?php
namespace Payum\Payex\Tests\Request\Api;

class CheckOrderTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\CheckOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
