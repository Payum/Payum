<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;

class PopulateKlarnaFromDetailsTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    public function testShouldAllowGetModelSetInConstructor()
    {
        $details = new \ArrayObject();
        $klarna = new \Klarna();

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $this->assertSame($details, $request->getModel());
    }

    public function testShouldAllowGetKlarnaSetInConstructor()
    {
        $details = new \ArrayObject();
        $klarna = new \Klarna();

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $this->assertSame($klarna, $request->getKlarna());
    }
}
