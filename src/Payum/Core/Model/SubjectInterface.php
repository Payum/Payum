<?php

declare(strict_types=1);

namespace Payum\Core\Model;

/**
 * The thing a command operates on: a payment, a payout, or whatever a gateway adds.
 *
 * Core needs two things from it and nothing more — details it can read and write, and a class it can look
 * a storage up by. Everything specific, an amount or a recipient, is the handler's business.
 *
 * Named for the role rather than the capability. If a middleware ever needs the amount or the currency
 * without knowing what it is holding, this is where they would move: PaymentInterface and PayoutInterface
 * both declare them already, so adding them here would break nothing.
 */
interface SubjectInterface extends DetailsAggregateInterface, DetailsAwareInterface
{
}
