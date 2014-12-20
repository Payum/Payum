<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails;

class PopulateKlarnaFromDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\PopulateKlarnaFromDetails');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithArrayModelAndKlarnaAsArguments()
    {
        new PopulateKlarnaFromDetails(new \ArrayObject(), new \Klarna());
    }

    /**
     * @test
     */
    public function shouldAllowGetModelSetInConstructor()
    {
        $details = new \ArrayObject();
        $klarna = new \Klarna();

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $this->assertSame($details, $request->getModel());
    }

    /**
     * @test
     */
    public function shouldAllowGetKlarnaSetInConstructor()
    {
        $details = new \ArrayObject();
        $klarna = new \Klarna();

        $request = new PopulateKlarnaFromDetails($details, $klarna);

        $this->assertSame($klarna, $request->getKlarna());
    }
}
