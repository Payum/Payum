<?php
namespace Payum\Payex\Tests\Request\Api;

class CheckOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\CheckOrder');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
