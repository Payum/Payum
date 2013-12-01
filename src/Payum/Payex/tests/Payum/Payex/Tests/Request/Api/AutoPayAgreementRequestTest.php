<?php
namespace Payum\Payex\Tests\Request\Api;

class AutoPayAgreementRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\AutoPayAgreementRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
    }
}