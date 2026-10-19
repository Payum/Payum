<?php

declare(strict_types=1);

namespace Payum\Core\Tests\Bc;

use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\FilesystemStorage;

/**
 * @extends FilesystemStorage<TokenInterface>
 */
final class BcTokenStorage extends FilesystemStorage
{
    public function __construct(string $storageDir)
    {
        parent::__construct($storageDir, BcToken::class, 'hash');
    }
}
