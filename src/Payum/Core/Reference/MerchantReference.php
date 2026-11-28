<?php

declare(strict_types=1);

namespace Payum\Core\Reference;

use Payum\Core\Exception\LogicException;
use Payum\Core\Middleware\SubjectIdentity;
use Payum\Core\Storage\IdentityInterface;

final class MerchantReference
{
    public const NAMESPACE = 'payum';

    /**
     * @throws LogicException when the identity has no id
     */
    public static function of(IdentityInterface $identity): string
    {
        $key = SubjectIdentity::key($identity)
            ?? throw new LogicException('The subject has no identity to build a reference from; save the subject before dispatching.');

        return 'py_' . substr(hash('sha256', $key), 0, 32);
    }
}
