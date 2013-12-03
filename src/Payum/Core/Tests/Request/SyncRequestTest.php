<?php
namespace Payum\Core\Tests\Request;

class SyncRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Core\Request\SyncRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelRequest'));
    }
}