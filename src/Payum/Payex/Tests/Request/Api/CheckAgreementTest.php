<?php
namespace Payum\Payex\Tests\Request\Api;

class CheckAgreementTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass(\Payum\Payex\Request\Api\CheckAgreement::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Request\Generic::class));
    }
}
