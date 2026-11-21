<?php

declare(strict_types=1);

namespace Payum\Core\Bridge\Doctrine;

use Payum\Core\Model\Customer;
use Payum\Core\Model\CustomerInterface;
use Payum\Core\Model\StoredPaymentMethod;
use Payum\Core\Model\StoredPaymentMethodInterface;

/**
 * The shipped mappings reference these interfaces. Feed this map to Doctrine's `resolve_target_entities`
 * (ORM) or `resolve_target_documents` (ODM), replacing a model with your own class where you have one.
 */
final class ResolveTargets
{
    /**
     * @var array<class-string, class-string>
     */
    public const DEFAULTS = [
        CustomerInterface::class => Customer::class,
        StoredPaymentMethodInterface::class => StoredPaymentMethod::class,
    ];
}
