<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Result;

use Payum\Core\Result\Acknowledgement;
use PHPUnit\Framework\TestCase;

final class AcknowledgementTest extends TestCase
{
    public function testShouldDefaultToNoContent(): void
    {
        $acknowledgement = Acknowledgement::noContent();

        $this->assertSame(204, $acknowledgement->status);
        $this->assertSame('', $acknowledgement->body);
        $this->assertSame([], $acknowledgement->headers);
    }

    public function testShouldCarryABodyThePspInsistsOn(): void
    {
        $acknowledgement = Acknowledgement::ok('[accepted]');

        $this->assertSame(200, $acknowledgement->status);
        $this->assertSame('[accepted]', $acknowledgement->body);
    }

    public function testShouldCarryHeadersWhenBuiltDirectly(): void
    {
        $acknowledgement = new Acknowledgement(200, '{"ok":true}', [
            'Content-Type' => 'application/json',
        ]);

        $this->assertSame(200, $acknowledgement->status);
        $this->assertSame('{"ok":true}', $acknowledgement->body);
        $this->assertSame([
            'Content-Type' => 'application/json',
        ], $acknowledgement->headers);
    }
}
