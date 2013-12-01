<?php
namespace Payum\Payex\Tests\Request\Api;

class DeleteAgreementRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\DeleteAgreementRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Request\BaseModelRequest'));
    }
}