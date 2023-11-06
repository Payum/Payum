<?php
namespace Payum\Payex\Tests\Request\Api;

class InitializeOrderTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\InitializeOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
