<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use Payum\Core\Model\Token;
use function sprintf;

/**
 * Numbered rather than random, so a URL or a signature computed over a token's hash compares equal from
 * one run to the next.
 */
class BcToken extends Token
{
    public static int $created = 0;

    public function __construct()
    {
        parent::__construct();

        $this->hash = sprintf('bc-token-%d', ++self::$created);
    }
}
