<?php
namespace Payum\Core\Tests\Request;

class GetHttpQueryRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassArrayObject()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\GetHttpQueryRequest');

        $this->assertTrue($rc->isSubclassOf('ArrayObject'));
    }
}
