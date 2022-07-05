<?php

namespace Payum\Klarna\Invoice\Tests\Request\Api;

use ArrayObject;
use Klarna;
use Payum\Core\Request\Generic;
use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PopulateKlarnaFromDetailsTest extends TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new ReflectionClass(PopulateKlarnaFromDetails::class);

        $this->assertTrue($rc->isSubclassOf(Generic::class));
    }

    public function testShouldAllowGetModelSetInConstructor()
    {
        $details = new ArrayObject();
        $klarna = new Klarna();

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $this->assertSame($details, $request->getModel());
    }

    public function testShouldAllowGetKlarnaSetInConstructor()
    {
        $details = new ArrayObject();
        $klarna = new Klarna();

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $this->assertSame($klarna, $request->getKlarna());
    }
}
