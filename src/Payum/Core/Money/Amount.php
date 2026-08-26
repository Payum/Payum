<?php

declare(strict_types=1);

namespace Payum\Core\Money;

use Money\Currencies\CurrencyList;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Payum\Core\Exception\LogicException;
use Payum\Core\Model\MoneyAwareInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Model\PayoutInterface;
use function method_exists;
use function sprintf;

/**
 * Maps between the minor-units-plus-currency-code pair Payum's models carry and a {@see Money}.
 *
 * A model does not have to implement {@see MoneyAwareInterface} for this to work — the pair of accessors
 * every 1.x model already has is enough — which is what lets an application move to Money without
 * touching its entities.
 */
final class Amount
{
    /**
     * What a model is for, or null when it names no currency or no amount.
     */
    public static function of(?object $subject): ?Money
    {
        if ($subject instanceof MoneyAwareInterface) {
            return $subject->getMoney();
        }

        if ($subject instanceof PaymentInterface || $subject instanceof PayoutInterface) {
            return self::fromMinorUnits($subject->getTotalAmount(), $subject->getCurrencyCode());
        }

        return null;
    }

    /**
     * Writes an amount back, through whichever accessors the model has.
     *
     * @throws LogicException when it has neither
     */
    public static function assign(object $subject, ?Money $money): void
    {
        if ($subject instanceof MoneyAwareInterface) {
            $subject->setMoney($money);

            return;
        }

        if (! method_exists($subject, 'setTotalAmount') || ! method_exists($subject, 'setCurrencyCode')) {
            throw new LogicException(sprintf(
                '%s has nowhere to put an amount. Implement %s, or give it setTotalAmount() and setCurrencyCode().',
                $subject::class,
                MoneyAwareInterface::class,
            ));
        }

        $subject->setTotalAmount($money instanceof Money ? self::toMinorUnits($money) : null);
        $subject->setCurrencyCode($money?->getCurrency()->getCode());
    }

    /**
     * Null unless both halves are there: an amount without a currency is not money.
     */
    public static function fromMinorUnits(int|string|null $amount, ?string $currencyCode): ?Money
    {
        if (null === $amount || null === $currencyCode || '' === $currencyCode) {
            return null;
        }

        return new Money($amount, new Currency($currencyCode));
    }

    /**
     * @throws LogicException when the amount is too large for an integer, which a currency with many
     *                        decimal places can reach
     */
    public static function toMinorUnits(Money $money): int
    {
        $amount = $money->getAmount();

        if ((string) (int) $amount !== $amount) {
            throw new LogicException(sprintf(
                '%s %s does not fit in an integer. Read it as a %s instead.',
                $amount,
                $money->getCurrency()->getCode(),
                Money::class,
            ));
        }

        return (int) $amount;
    }

    /**
     * The amount as an exact decimal string — '1.23', '0.00000001'.
     *
     * Dividing by 10 ** $subunit gives a float, which is what makes a currency with more than a couple of
     * decimal places come out wrong.
     *
     * @param non-empty-string $currencyCode
     * @param int<0, max>|null $subunit decimal places, needed only for a currency ISO 4217 does not list
     */
    public static function toDecimalString(int|string|null $amount, string $currencyCode, ?int $subunit = null): string
    {
        $currencies = null === $subunit ? new ISOCurrencies() : new CurrencyList([
            $currencyCode => $subunit,
        ]);

        return (new DecimalMoneyFormatter($currencies))->format(new Money($amount ?? 0, new Currency($currencyCode)));
    }
}
