<?php
namespace Payum\Payex\Tests\Request\Api;

class CreateAgreementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelAware()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\CreateAgreementRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelAware'));
    }
}