<?php

namespace Payum\Core\Bridge\Symfony;

/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
final class PayumEvents
{
    const PAYMENT_PRE_EXECUTE = 'payum.payment.pre_execute';

    const PAYMENT_EXECUTE = 'payum.payment.execute';

    const PAYMENT_POST_EXECUTE = 'payum.payment.post_execute';

    const PAYMENT_REPLY = 'payum.payment.reply';

    const PAYMENT_EXCEPTION = 'payum.payment.exception';
}
