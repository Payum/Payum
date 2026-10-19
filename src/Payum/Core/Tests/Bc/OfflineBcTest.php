<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

final class OfflineBcTest extends BcTestCase
{
    protected static function scenarios(): string
    {
        return 'offline';
    }
}
