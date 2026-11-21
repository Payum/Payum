<?php

declare(strict_types=1);

namespace Payum\Core\Reference;

use Doctrine\Persistence\Proxy;
use Payum\Core\Storage\IdentityInterface;

final class MerchantReference
{
    public const NAMESPACE = 'payum';

    public static function of(IdentityInterface $identity): string
    {
        $class = $identity->getClass();

        if (is_a($class, Proxy::class, true)) {
            $class = (string) get_parent_class($class);
        }

        return 'py_' . substr(hash('sha256', $class . ':' . $identity->getId()), 0, 32);
    }
}
