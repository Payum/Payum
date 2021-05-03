<?php
namespace Payum\Klarna\Invoice\Tests\Request\Api;

use Payum\Klarna\Invoice\Request\Api\Activate;
use PHPUnit\Framework\TestCase;

class ActivateTest extends TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseOrder()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Invoice\Request\Api\Activate');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
