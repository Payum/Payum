<?php
namespace Payum\Stripe\Tests\Request\Api;

use Payum\Stripe\Request\Api\CreateChargeRequest;

class CreateChargeRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfBaseModelRequest()
    {
        $rc = new \ReflectionClass('Payum\Stripe\Request\Api\CreateChargeRequest');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Request\BaseModelRequest'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithModelAsFirstArgument()
    {
        new CreateChargeRequest($model = array());
    }
}