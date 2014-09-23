<?php
namespace Payum\Paypal\ExpressCheckout\Nvp\Tests\Request\Api;

class DoReferenceTransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfGeneric()
    {
        $rc = new \ReflectionClass('Payum\Paypal\ExpressCheckout\Nvp\Request\Api\DoReferenceTransaction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\Generic'));
    }
}
