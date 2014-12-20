<?php
namespace Payum\Payex\Tests\Request\Api;

class AutoPayAgreementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\AutoPayAgreement');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
