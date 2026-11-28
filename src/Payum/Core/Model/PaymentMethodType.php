<?php

declare(strict_types=1);

namespace Payum\Core\Model;

enum PaymentMethodType: string
{
    case Card = 'card';

    case BankDebit = 'bank_debit';

    case Wallet = 'wallet';

    case Other = 'other';
}
