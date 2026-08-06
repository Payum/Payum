<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bridge\Symfony\Reply;

use Payum\Core\Bridge\Symfony\Reply\HttpResponse;
use Payum\Core\Reply\Base;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

final class HttpResponseTest extends TestCase
{
    public function testShouldBeSubClassOfBaseReply(): void
    {
        $rc = new ReflectionClass(HttpResponse::class);

        $this->assertTrue($rc->isSubclassOf(Base::class));
    }

    public function testShouldAllowGetResponseSetInConstructor(): void
    {
        $expectedResponse = new Response();

        $request = new HttpResponse($expectedResponse);

        $this->assertSame($expectedResponse, $request->getResponse());
    }
}
