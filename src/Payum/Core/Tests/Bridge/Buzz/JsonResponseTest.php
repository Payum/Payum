<?php
namespace Payum\Core\Tests\Bridge\Buzz;

use Payum\Core\Bridge\Buzz\JsonResponse;

class JsonResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldDecodeJson()
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';

        $response = new JsonResponse();
        $response->setContent(json_encode($obj));

        $this->assertEquals($obj, $response->getContentJson());
    }

    /**
     * @test
     */
    public function shouldDecodeJsonWithBOM()
    {
        $obj = new \stdClass();
        $obj->foo = 'bar';

        $response = new JsonResponse();
        $response->setContent(pack('CCC', 239, 187, 191).json_encode($obj));

        $this->assertEquals($obj, $response->getContentJson());
    }
}
