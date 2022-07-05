<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

class CreditPartTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass(\Payum\Klarna\Invoice\Request\Api\CreditPart::class);

        $this->assertTrue($rc->isSubclassOf(\Payum\Core\Request\Generic::class));
    }
}
