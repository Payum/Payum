<?php

/**
 * @author Marc Pantel <pantel.m@gmail.com>
 */

namespace Payum\Core\Bridge\Symfony\Validator\Constraints;

use DateTime;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

@trigger_error('The ' . __NAMESPACE__ . '\CreditCardDate class is deprecated since version 2.0 and will be removed in 3.0. Use the same class from Payum/PayumBundle instead.', E_USER_DEPRECATED);

/**
 * @deprecated since 2.0. Use the same class from Payum/PayumBundle instead.
 */
class CreditCardDate extends Constraint
{
    public $minMessage = 'validator.credit_card.invalidDate';

    public $invalidMessage = 'validator.credit_card.invalidDate';

    public $min;

    /**
     * @param array<string, mixed>|null $options
     * @param string[]|null             $groups
     */
    #[HasNamedArguments]
    public function __construct(
        ?array $options = null,
        ?string $min = null,
        ?string $minMessage = null,
        ?string $invalidMessage = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        // Symfony 8 no longer reads an options array in Constraint::__construct(), so it is unpacked here.
        $options ??= [];
        parent::__construct(null, $groups ?? $options['groups'] ?? null, $payload ?? $options['payload'] ?? null);

        $this->minMessage = $minMessage ?? $options['minMessage'] ?? $this->minMessage;
        $this->invalidMessage = $invalidMessage ?? $options['invalidMessage'] ?? $this->invalidMessage;
        $min ??= $options['min'] ?? null;

        if (null === $min) {
            throw new MissingOptionsException('Either option "min" must be given for constraint ' . self::class, ['min']);
        }

        $this->min = new DateTime($min);
        $this->min->modify('last day of this month');
    }
}
