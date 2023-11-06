<?php
namespace Payum\Payex\Tests\Request\Api;

class CompleteOrderTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\CompleteOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
