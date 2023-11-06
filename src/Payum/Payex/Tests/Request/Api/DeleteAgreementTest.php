<?php
namespace Payum\Payex\Tests\Request\Api;

class DeleteAgreementTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Payex\Request\Api\DeleteAgreement');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
