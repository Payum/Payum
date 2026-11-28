<?php

declare(strict_types=1);

namespace Payum\Core\Model;

enum SubscriptionSchedule: string
{
    /**
     * The PSP schedules and charges the renewals.
     */
    case Gateway = 'gateway';

    /**
     * The application charges each renewal itself, with a CaptureCommand.
     */
    case Application = 'application';
}
