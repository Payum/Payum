<?php
namespace Payum\Payex\Tests\Request\Api;

class CreateAgreementTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\CreateAgreement');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
