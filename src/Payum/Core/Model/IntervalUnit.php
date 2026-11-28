<?php

declare(strict_types=1);

namespace Payum\Core\Model;

enum IntervalUnit: string
{
    case Day = 'day';

    case Week = 'week';

    case Month = 'month';

    case Year = 'year';
}
