<?php
namespace Payum\Payex\Tests\Request\Api;

class AutoPayAgreementTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\AutoPayAgreement');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
